<?php

class Aoe_AsyncCache_Model_JobCollection extends Varien_Data_Collection
{
    /**
     * Generate item id
     *
     * @param Varien_Object|Aoe_AsyncCache_Model_Job $item
     * @return string
     */
    protected function _getItemId(Varien_Object $item)
    {
        return md5($item->getMode() . $item->getTags());
    }

    /**
     * Check for duplicates before adding new job to the collection
     *
     * @param Varien_Object|Aoe_AsyncCache_Model_Job $item
     * @return $this
     */
    public function addItem(Varien_Object $item)
    {
        $itemId = $this->_getItemId($item);
        if (!isset($this->_items[$itemId])) {
            $this->_items[$itemId] = $item;
        }

        return $this;
    }
}
