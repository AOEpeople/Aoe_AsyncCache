<?php

/**
 * Cleaner
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Cleaner extends Mage_Core_Model_Abstract
{
    /**@+
     * Purge message patterns
     *
     * @var string
     */
    const PROCESSED_MESSAGE_PATTERN     = '[ASYNCCACHE] MODE: %s, DURATION: %s sec, TAGS: %s';
    const NOT_PROCESSED_MESSAGE_PATTERN = "[ASYNCCACHE] Couldn't process job: MODE: %s, TAGS: %s";
    /**@-*/

    /**
     * Supported job modes
     *
     * @var string[]
     */
    protected $_supportedJobModes = array(
        Zend_Cache::CLEANING_MODE_ALL,
        Zend_Cache::CLEANING_MODE_OLD,
        Zend_Cache::CLEANING_MODE_MATCHING_TAG,
        Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
        Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG
    );

    /**
     * Process the queue
     */
    public function processQueue()
    {
        /** @var Aoe_AsyncCache_Helper_Data $helper */
        $helper = Mage::helper('aoeasynccache');

        /** @var $collection Aoe_AsyncCache_Model_Resource_Asynccache_Collection */
        $collection = Mage::getModel('aoeasynccache/asynccache')->getCollection();
        $collection->fetchItemsFromQueue($helper->getSelectLimit());
        if ($collection->count() > 0) {
            $jobCollection = $this->extractJobs($collection->getItems());
            /** @var $jobCollection Aoe_AsyncCache_Model_JobCollection */

            // give other modules (e.g. Aoe_Static) to process jobs instead
            Mage::dispatchEvent('aoeasynccache_processqueue_preprocessjobcollection',
                array('jobCollection' => $jobCollection)
            );

            /** @var $job Aoe_AsyncCache_Model_Job */
            foreach ($jobCollection as $job) {
                if (!$job->getIsProcessed()) {
                    if (in_array($job->getMode(), $this->_supportedJobModes)) {
                        $startTime = time();
                        Mage::app()->getCache()->clean($job->getMode(), $job->getTags(), true);
                        $job->setDuration(time() - $startTime);
                        $job->setIsProcessed(true);

                        Mage::log(
                            sprintf(self::PROCESSED_MESSAGE_PATTERN, $job->getMode(), $job->getDuration(),
                                implode(', ', $job->getTags())
                            )
                        );
                    }
                }
            }

            // give other modules (e.g. Aoe_Static) to process jobs instead
            Mage::dispatchEvent('aoeasynccache_processqueue_postprocessjobcollection',
                array('jobCollection' => $jobCollection)
            );

            // check what jobs weren't processed by code in any observer
            /** @var $job Aoe_AsyncCache_Model_Job */
            foreach ($jobCollection as $job) {
                if (!$job->getIsProcessed()) {
                    Mage::log(
                        sprintf(self::NOT_PROCESSED_MESSAGE_PATTERN, $job->getMode(), implode(', ', $job->getTags())),
                        Zend_Log::ERR
                    );
                }
            }
        }

        // disabling asynccache (clear cache requests will be processed right away)
        // for all following requests in this script call
        Mage::register('disableasynccache', true, true);
    }

    /**
     * Extract jobs
     * Combines job to reduce cache operations
     *
     * @param array|Aoe_AsyncCache_Model_Asynccache[] $items
     * @return Aoe_AsyncCache_Model_JobCollection
     */
    public function extractJobs(array $items)
    {
        /** @var $jobCollection Aoe_AsyncCache_Model_JobCollection */
        $jobCollection = Mage::getModel('aoeasynccache/jobCollection');

        $matchingAnyTag = array();
        foreach ($items as $item) {
            $mode = $item->getMode();
            $tags = $item->getTags();

            /** @var $job Aoe_AsyncCache_Model_Job */
            $job = Mage::getModel('aoeasynccache/job');
            $job->setParameters($mode, $tags);

            if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
                $jobCollection->addItem($job);
                return $jobCollection; // no further processing needed as we're going to clean everything anyway
            } elseif ($mode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
                // collect tags and add to job collection later
                $matchingAnyTag = array_merge($matchingAnyTag, $tags);
            } elseif ($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG && count($tags) <= 1) {
                // collect tags and add to job collection later
                $matchingAnyTag = array_merge($matchingAnyTag, $tags);
            } else {
                // everything else will be added to the job collection
                $jobCollection->addItem($job);
            }
        }

        // processed collected tags
        $matchingAnyTag = array_unique($matchingAnyTag);
        if (count($matchingAnyTag) > 0) {
            /** @var $job Aoe_AsyncCache_Model_Job */
            $job = Mage::getModel('aoeasynccache/job');
            $job->setParameters(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $matchingAnyTag);

            $jobCollection->addItem($job);
        }

        return $jobCollection;
    }
}
