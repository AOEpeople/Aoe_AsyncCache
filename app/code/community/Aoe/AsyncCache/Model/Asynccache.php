<?php

/**
 * Async class
 *
 * @author Fabrizio Branca
 *
 * @method int getId()
 * @method Aoe_AsyncCache_Model_Asynccache setId(int $id)
 * @method int getTstamp()
 * @method Aoe_AsyncCache_Model_Asynccache setTstamp(int $timeStamp)
 * @method string getMode()
 * @method Aoe_AsyncCache_Model_Asynccache setMode(string $mode)
 * @method string getTags()
 * @method string getTrace()
 * @method Aoe_AsyncCache_Model_Asynccache setTrace(string $trace)
 * @method string getStatus()
 * @method Aoe_AsyncCache_Model_Asynccache setStatus(string $status)
 * @method string getProcessed()
 * @method Aoe_AsyncCache_Model_Asynccache setProcessed(string $message)
 */
class Aoe_AsyncCache_Model_Asynccache extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('aoeasynccache/asynccache');
    }

    /**
     * Set tags
     *
     * @param array|string $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $this->setData('tags', is_array($tags) ? implode(',', $tags) : $tags);

        return $this;
    }
}
