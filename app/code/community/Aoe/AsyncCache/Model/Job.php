<?php

/**
 * @method array getTags()
 * @method string getMode()
 * @method setDuration()
 * @method getDuration()
 * @method getIsProcessed()
 * @method setIsProcessed()
 */
class Aoe_AsyncCache_Model_Job extends Varien_Object
{
    /**
     * Set mode and tags
     *
     * @param string $mode
     * @param array $tags
     * @return $this
     */
    public function setParameters($mode, array $tags)
    {
        if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
            $tags = array(); // we don't need any tags for mode 'all'
        }

        $this->setData('mode', $mode)
            ->setData('tags', $tags);

        return $this;
    }
}
