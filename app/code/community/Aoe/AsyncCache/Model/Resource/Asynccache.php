<?php

/**
 * Asynccache
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Resource_Asynccache extends Mage_Core_Model_Resource_Db_Abstract
{
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
     * Overwritten save method, updates data on duplicate key
     *
     * @param Mage_Core_Model_Abstract $object
     * @return $this
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_beforeSave($object);
        $this->_getWriteAdapter()->insertIgnore($this->getMainTable(),
            $this->_prepareDataForSave($object)
        );
        $this->_afterSave($object);

        return $this;
    }

    /**
     * Fetch items from queue
     *
     * @param int $limit if 0 - no limit
     * @return array
     */
    public function fetchItemsFromQueue($limit = 0)
    {
        $marker = uniqid(mt_rand() . '_', true);

        try {
            $this->_getWriteAdapter()->beginTransaction();
            $this->_markRows($marker, $limit);
            $rows = $this->_fetchMarkedRows($marker);
            $this->_deleteMarkedRows($marker);
            $this->_getWriteAdapter()->commit();

            return $rows;
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getWriteAdapter()->rollBack();

            return array();
        }
    }

    /**
     * Mark rows to fetch and delete with hash
     * (i.e. update marker field rows which match criteria with generated marker string)
     *
     * @param string $marker
     * @param int $limit
     * @return bool
     */
    protected function _markRows($marker, $limit)
    {
        $query = <<<SQL
            UPDATE `{$this->getMainTable()}`
            SET `marker` = '{$marker}'
            WHERE `marker` IS NULL
SQL;
        if ($limit) {
            $query .= <<<SQL
                ORDER BY `tstamp` ASC
                LIMIT {$limit}
SQL;
        }

        return $this->_getWriteAdapter()->query($query)->execute();
    }

    /**
     * Fetch marked rows
     *
     * @param string $marker
     * @return bool
     */
    protected function _fetchMarkedRows($marker)
    {
        $query = <<<SQL
            SELECT `id`, `mode`, `tags`
            FROM {$this->getMainTable()}
            WHERE marker = '{$marker}'
SQL;

        return $this->_getWriteAdapter()->query($query)->fetchAll();
    }

    /**
     * Delete marked rows
     *
     * @param string $marker
     * @return bool
     */
    protected function _deleteMarkedRows($marker)
    {
        $query = <<<SQL
            DELETE
            FROM {$this->getMainTable()}
            WHERE marker = '{$marker}'
SQL;

        return $this->_getWriteAdapter()->query($query)->execute();
    }
}
