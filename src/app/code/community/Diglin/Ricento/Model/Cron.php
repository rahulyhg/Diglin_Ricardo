<?php
/**
 * ricardo.ch AG - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_Ricento
 * @copyright   Copyright (c) 2014 ricardo.ch AG (http://www.ricardo.ch)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Diglin_Ricento_Model_Cron
 */
class Diglin_Ricento_Model_Cron
{
    protected $_syncProcess = array(
        //Diglin_Ricento_Model_Sync_Job::TYPE_CHECK_LIST, //** Check list before to sync to ricardo.ch - @deprecated move to Diglin_Ricento_Adminhtml_Products_ListingController to start quickly the check
        Diglin_Ricento_Model_Sync_Job::TYPE_LIST, //** List to ricardo.ch
        Diglin_Ricento_Model_Sync_Job::TYPE_STOP, //** Stop the list on ricardo.ch if needed
        //Diglin_Ricento_Model_Sync_Job::TYPE_RELIST //** Relist to ricardo.ch
    );

    protected $_asyncProcess = array(
        Diglin_Ricento_Model_Sync_Job::TYPE_SYNCLIST, //** Sync List before getting orders
        Diglin_Ricento_Model_Sync_Job::TYPE_ORDER //** Get new orders
    );

    /**
     * Process Cron tasks - should be run in a short period of time
     */
    public function process()
    {
        if (!Mage::helper('diglin_ricento')->isEnabled() || $this->_isTokenExpired()) {
            return;
        }

        ini_set('memory_limit', '512M');

        try {
            foreach ($this->_syncProcess as $jobType) {
                $this->_dispatch($jobType);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Process Cron tasks which needs to be run on a longer period of time
     */
    public function async()
    {
        if (!Mage::helper('diglin_ricento')->isEnabled() || $this->_isTokenExpired()) {
            return;
        }

        ini_set('memory_limit', '512M');

        try {
            foreach ($this->_asyncProcess as $jobType) {
                $this->_dispatch($jobType);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $this->_processCleanupJobs();
    }

    /**
     * Clean old jobs passed on the last X days
     *
     * @return $this
     */
    protected function _processCleanupJobs()
    {
        if (Mage::getStoreConfigFlag(Diglin_Ricento_Helper_Data::CFG_CLEAN_JOBS_ENABLED)) {
            $daysKeep = (int)Mage::getStoreConfig(Diglin_Ricento_Helper_Data::CFG_CLEAN_JOBS_KEEP_DAYS);

            try {
                $coreResource = Mage::getSingleton('core/resource');
                $write = $coreResource->getConnection('core_write');

                $select = $write->select()
                    ->from(array('main_table' => $coreResource->getTableName('ricento_sync_job')))
                    ->where('((TO_DAYS(main_table.created_at) + ?) < TO_DAYS(now()))', $daysKeep);

                $query = $select->deleteFromSelect('main_table');
                $write->query($query);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * @return Diglin_Ricento_Model_Dispatcher
     */
    private function _getDisptacher()
    {
        return Mage::getSingleton('diglin_ricento/dispatcher');
    }

    /**
     * @param int $type
     * @return $this
     */
    private function _dispatch($type)
    {
        return $this->_getDisptacher()->dispatch($type)->proceed();
    }

    /**
     * @return bool
     */
    private function _isTokenExpired()
    {
        $helper = Mage::helper('diglin_ricento/api');

        // @todo in case of real multi website support, add website parameter

        if ($helper->apiTokenCredentialValidation() && !$helper->isMerchantNotifiedApiAuthorization()) {
            $helperTools = Mage::helper('diglin_ricento/tools');
            $helperTools->sendMerchantAuthorizationNotification(array(
                'shop_url' => Mage::helper('adminhtml')->getUrl('adminhtml')
            ));

            $token = Mage::getModel('diglin_ricento/api_token')
                ->loadByWebsiteAndTokenType(Diglin\Ricardo\Services\Security::TOKEN_TYPE_IDENTIFIED, Mage::app()->getWebsite()->getId());

            if ($token->getId()) {
                $token
                    ->setMerchantNotified(1)
                    ->save();
            }
        }

        if ($helper->apiExpired()) {
            return true;
        }

        return false;
    }
}