<?php

/**
 * Async controller
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Adminhtml_AsyncController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Process the queue
     * This action is called from a button in the "Cache Management" module.
     * Afterwards it redirects back to that module
     */
    public function processAction()
    {
        $summary = Mage::getModel('aoeasynccache/cleaner')->processQueue();
        if (is_array($summary)) {
            foreach ($summary as $processedEntry) {
                $this->_getSession()->addSuccess($processedEntry);
            }
        }
        $this->_redirect('*/cache/index');
    }

    /**
     * Process the queue
     * This action is called from a button in the "Cache Management" module.
     * Afterwards it redirects back to that module
     */
    public function flushAllNowAction()
    {

        Mage::dispatchEvent('adminhtml_cache_flush_all');
        Mage::app()->getCacheInstance()->flush();
        $this->_getSession()->addSuccess(Mage::helper('aoeasynccache')->__("The cache storage has been flushed."));

        $summary = Mage::getModel('aoeasynccache/cleaner')->processQueue();
        if (is_array($summary)) {
            foreach ($summary as $processedEntry) {
                    $this->_getSession()->addSuccess($processedEntry);
                }
        }
        $this->_redirect('*/cache/index');
    }

    /**
     * Delete a async entry
     * This action is called from a button in the "Cache Management" module.
     * Afterwards it redirects back to that module
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $async = Mage::getModel('aoeasynccache/asynccache')->load($id);
        $async->setStatus('deleted');
        $async->setProcessed(time());
        $async->save();
        $this->_getSession()->addSuccess(Mage::helper('aoeasynccache')->__('Deleted item "%s"', $id));
        $this->_redirect('*/cache/index');
    }
}
