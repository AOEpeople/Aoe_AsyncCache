<?php

class Aoe_AsyncCache_Block_Adminhtml_AsyncControl extends Mage_Adminhtml_Block_Template
{
    /**
     * Get collection of async objects
     *
     * @return Aoe_AsyncCache_Model_Resource_Asynccache_Collection
     */
    public function getAsyncCollection()
    {
        /** @var Aoe_AsyncCache_Helper_Data $helper */
        $helper = Mage::helper('aoeasynccache');

        /** @var $collection Aoe_AsyncCache_Model_Resource_Asynccache_Collection */
        $collection = Mage::getModel('aoeasynccache/asynccache')->getCollection();
        $collection->orderByTstampAsc($helper->getSelectLimit());

        return $collection;
    }

    /**
     * Get clean cache jobs extracted from aoe_asynccache table
     *
     * @return Aoe_AsyncCache_Model_JobCollection
     */
    public function getExtractedJobs()
    {
        /** @var Aoe_AsyncCache_Model_Cleaner $asyncCacheCleaner */
        $asyncCacheCleaner = Mage::getSingleton('aoeasynccache/cleaner');

        return $asyncCacheCleaner->extractJobs($this->getAsyncCollection()->getItems());
    }
}
