<?php

/**
 * Asynccache
 * 
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Resource_Asynccache extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aoeasynccache/asynccache', 'id');
    }

    /**
     * Overwritten save method, ignore on duplicate key
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Aoe_AsyncCache_Model_Resource_Asynccache
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_serializeFields($object);
        $this->_beforeSave($object);

        $this->_getWriteAdapter()->insertIgnore($this->getMainTable(),
            $this->_prepareDataForSave($object)
        );
        $this->unserializeFields($object);
        $this->_afterSave($object);

        return $this;
    }
}
