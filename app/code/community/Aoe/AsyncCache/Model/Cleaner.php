<?php

/**
 * Cleaner
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Cleaner extends Mage_Core_Model_Abstract
{
    /**
     * Supported job modes
     *
     * @var array
     */
    protected $_supportedJobModes = array(
        Zend_Cache::CLEANING_MODE_ALL,
        Zend_Cache::CLEANING_MODE_OLD,
        Zend_Cache::CLEANING_MODE_MATCHING_TAG,
        Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
        Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG
    );

    /**
     * Enterprise_PageCache flag
     *
     * @var null|boolean
     */
    protected $_enterprisePageCacheEnabledFlag = null;

    /**
     * determine wether Enterprise_PageCache is available or not
     *
     * @return boolean
     */
    protected function _enterprisePageCacheEnabled()
    {
        if (is_null($this->_enterprisePageCacheEnabledFlag)) {
            $this->_enterprisePageCacheEnabledFlag = is_object(Mage::getConfig()->getNode('modules/Enterprise_Pbridge')) && class_exists('Enterprise_PageCache_Model_Cache');
        }

        return $this->_enterprisePageCacheEnabledFlag;
    }

    /**
     * Process the queue
     *
     * @return array|null
     */
    public function processQueue()
    {
        $summary = array();

        $collection = $this->getUnprocessedEntriesCollection();
        if (count($collection) > 0) {
            $jobCollection = $collection->extractJobs();
            /** @var $jobCollection Aoe_AsyncCache_Model_JobCollection */

            // give other modules (e.g. Aoe_VarnishAsyncCache) to process jobs instead
            Mage::dispatchEvent('aoeasynccache_processqueue_preprocessjobcollection',
                array('jobCollection' => $jobCollection)
            );

            /** @var $job Aoe_AsyncCache_Model_Job */
            foreach ($jobCollection as $job) {
                if (!$job->getIsProcessed()) {
                    $mode = $job->getMode();
                    if (in_array($mode, $this->_supportedJobModes)) {
                        $startTime = time();
                        Mage::app()->getCache()->clean($job->getMode(), $job->getTags(), true);
                        $time = time() - $startTime;
                        $summary[] = sprintf('[ASYNCCACHE] MODE: %s, DURATION: %s sec, TAGS: %s',
                            $job->getMode(),
                            $time,
                            implode(', ', $job->getTags())
                        );

                        if ($this->_enterprisePageCacheEnabled()) {
                            $fpcStartTime = time();
                            Enterprise_PageCache_Model_Cache::getCacheInstance()->clean($job->getMode(), $job->getTags(), true);
                            $fpcTime = time() - $fpcStartTime;
                            $time += $fpcTime;
                            $summary[] = sprintf('[ASYNCCACHE::FPC] MODE: %s, DURATION: %s sec, TAGS: %s',
                                $job->getMode(),
                                $fpcTime,
                                implode(', ', $job->getTags())
                            );
                        }

                        $job->setDuration($time);
                        $job->setIsProcessed(true);
                    }
                }
            }

            // give other modules (e.g. Aoe_VarnishAsyncCache) to process jobs instead
            Mage::dispatchEvent('aoeasynccache_processqueue_postprocessjobcollection',
                array('jobCollection' => $jobCollection)
            );

            // check what jobs weren't processed by any code
            /** @var $job Aoe_AsyncCache_Model_Job */
            foreach ($jobCollection as $job) {
                if (!$job->getIsProcessed()) {
                    Mage::log(sprintf("[ASYNCCACHE] Couldn't process job: MODE: %s, TAGS: %s",
                        $job->getMode(),
                        implode(', ', $job->getTags())
                    ), Zend_Log::ERR);
                }
            }

            // delete all affected asynccache database rows
            /** @var $asynccache Aoe_AsyncCache_Model_Asynccache */
            foreach ($collection as $asynccache) {
                $asynccache->delete();
            }

            //$summary = $jobCollection->getSummary();
            Mage::log($summary);
        }

        // disabling asynccache (clear cache requests will be processed right away)
        // for all following requests in this script call
        Mage::register('disableasynccache', true, true);

        return $summary;
    }

    /**
     * Get all unprocessed entries
     *
     * @return Aoe_AsyncCache_Model_Resource_Asynccache_Collection
     */
    public function getUnprocessedEntriesCollection()
    {
        /** @var $collection Aoe_AsyncCache_Model_Resource_Asynccache_Collection */
        $collection = Mage::getModel('aoeasynccache/asynccache')->getCollection();
        $collection->addFieldToFilter('tstamp', array('lteq' => time()))
            ->addFieldToFilter('status', Aoe_AsyncCache_Model_Asynccache::STATUS_PENDING)
            ->addOrder('tstamp', Varien_Data_Collection::SORT_ORDER_ASC);

        // if configured, set limit to query
        $selectLimit = (int)Mage::getStoreConfig('system/aoeasynccache/select_limit');
        if ($selectLimit != 0) {
            $collection->setCurPage(1)
                ->setPageSize($selectLimit);
        }

        return $collection;
    }
}
