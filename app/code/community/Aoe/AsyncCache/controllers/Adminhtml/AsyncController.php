<?php

/**
 * Async controller
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Adminhtml_AsyncController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Process the queue.
     * This action is called from a button on the "Cache Management" page.
     * Afterwards redirect back to that page.
     */
    public function processAction()
    {
        /** @var Aoe_AsyncCache_Model_Cleaner $cleaner */
        $cleaner = Mage::getModel('aoeasynccache/cleaner');
        $cleaner->processQueue();
        $this->_getSession()->addSuccess(
            Mage::helper('aoeasynccache')->__("All items in the asynchronous queue were successfully processed")
        );
        $this->_redirect('*/cache/index');
    }

    /**
     * Process the queue.
     * This action is called from a button on the "Cache Management" page.
     * Afterward redirect back to that page.
     */
    public function flushAllNowAction()
    {
        Mage::dispatchEvent('adminhtml_cache_flush_all');
        Mage::app()->getCacheInstance()->flush();
        /** @var Aoe_AsyncCache_Model_Cleaner $cleaner */
        $cleaner = Mage::getModel('aoeasynccache/cleaner');
        $cleaner->processQueue();

        $this->_getSession()->addSuccess(
            Mage::helper('aoeasynccache')->__("Cache storage has been flushed successfully")
        );
        $this->_redirect('*/cache/index');
    }

    /**
     * Delete a async entry.
     * Afterwards redirect back to the "Cache Management" page.
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var Aoe_AsyncCache_Model_Asynccache $asyncCacheModel */
        $asyncCacheModel = Mage::getModel('aoeasynccache/asynccache');
        $asyncCacheModel->load((int)$id);
        if ($asyncCacheModel->getId()) {
            $asyncCacheModel->delete();
            $this->_getSession()->addSuccess(
                Mage::helper('aoeasynccache')->__('Deleted item with mode "%s" and tags "%s"',
                    $asyncCacheModel->getMode(), implode(',', $asyncCacheModel->getTags())
                )
            );
        } else {
            $this->_getSession()->addError(
                Mage::helper('aoeasynccache')->__('Item with id "%d" not found in the asynccache table', $id)
            );
        }
        $this->_redirect('*/cache/index');
    }
}
