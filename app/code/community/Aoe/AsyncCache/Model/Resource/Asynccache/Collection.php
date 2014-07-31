<?php

/**
 * Async collection
 *
 * @author Fabrizio Branca
 *
 * @method Aoe_AsyncCache_Model_Resource_Asynccache getResource() getResource()
 */
class Aoe_AsyncCache_Model_Resource_Asynccache_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aoeasynccache/asynccache');
    }

    /**
     * Add unprocessed entries filter
     *
     * @param int $limit
     * @return $this
     */
    public function orderByTstampAsc($limit = 0)
    {
        $this->addOrder('tstamp', Varien_Data_Collection::SORT_ORDER_ASC);

        if ($limit) {
            $this->setCurPage(1)
                ->setPageSize((int)$limit);
        }

        return $this;
    }

    /**
     * Fetch items from aoe_asynccache table (fetched items will be deleted from table)
     *
     * @param int $limit if 0 - no limit
     * @return array
     */
    public function fetchItemsFromQueue($limit = 0)
    {
        $this->resetData();
        $rows = $this->getResource()->fetchItemsFromQueue($limit);

        foreach ($rows as $row) {
            $item = $this->getNewEmptyItem();
            if ($this->getIdFieldName()) {
                $item->setIdFieldName($this->getIdFieldName());
            }
            $item->addData($row);
            $this->addItem($item);
        }

        $this->_setIsLoaded();
        return $this;
    }
}
