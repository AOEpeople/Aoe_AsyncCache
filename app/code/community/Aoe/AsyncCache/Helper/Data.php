<?php

/**
 * Cache cleaner helper
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Select limit config option xpath
     */
    const XML_PATH_SELECT_LIMIT = 'system/aoeasynccache/select_limit';

    /**
     * Select limit config option value
     *
     * @var int
     */
    protected $_selectLimit = null;

    /**
     * Get select limit (config option) value
     *
     * @return int
     */
    public function getSelectLimit()
    {
        if ($this->_selectLimit === null) {
            $this->_selectLimit = (int)Mage::getStoreConfig(self::XML_PATH_SELECT_LIMIT);
        }

        return $this->_selectLimit;
    }

    /**
     * Add new job to the aoe_asynccache table
     *
     * @param string $mode
     * @param array|string $tags
     * @throws Exception
     */
    protected function _addJob($mode, $tags)
    {
        /** @var $asyncCache Aoe_AsyncCache_Model_Asynccache */
        $asyncCache = Mage::getModel('aoeasynccache/asynccache');
        $asyncCache->setMode($mode)
            ->setTags($tags)
            ->save();
    }

    /**
     * Add new job to the aoe_asynccache table
     *
     * @param string $mode
     * @param array|string $tags
     * @param bool $jobPerTag
     * @throws Exception
     */
    public function addJob($mode, $tags, $jobPerTag = false)
    {
        if ($jobPerTag && is_array($tags)) {
            foreach ($tags as $tag) {
                $this->_addJob($mode, $tag);
            }
        } else {
            $this->_addJob($mode, $tags);
        }
    }
}
