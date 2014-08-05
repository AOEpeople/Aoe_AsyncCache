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
 */
class Aoe_AsyncCache_Model_Asynccache extends Mage_Core_Model_Abstract
{
    /**
     * Tags delimiter for implode/explode
     */
    const TAG_DELIMITER = ',';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('aoeasynccache/asynccache');
    }

    /**
     * Filter tags - remove empty and duplicate tags and then sort them
     *
     * @param array $unfilteredTags
     * @return array
     */
    protected function _prepareTagArray(array $unfilteredTags)
    {
        $tags = array_unique(array_filter(array_map('trim', $unfilteredTags)));
        sort($tags);

        return $tags;
    }

    /**
     * Set tags
     *
     * @param array|string $tags
     * @return $this
     */
    public function setTags($tags)
    {
        if (is_array($tags)) {
            $tagString = implode(self::TAG_DELIMITER, $this->_prepareTagArray($tags));
        } else {
            $tagString = $tags;
        }
        $this->setData('tags', $tagString);

        return $this;
    }

    /**
     * Get tags
     *
     * @return array
     */
    public function getTags()
    {
        return explode(self::TAG_DELIMITER, $this->getData('tags'));
    }
}
