<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    Ricento
 * @package     Diglin_Ricardo
 * @copyright   Copyright (c) 2011-2014 Diglin (http://www.diglin.com)
 */

use \Diglin\Ricardo\Api;
use \Diglin\Ricardo\Config;
use \Diglin\Ricardo\Service;

/**
 * Class Diglin_Ricento_Model_Api_Services_Abstract
 */
abstract class Diglin_Ricento_Model_Api_Services_Abstract extends Varien_Object
{
    /**
     * @var string
     */
    protected $_registryKey =  'serviceManager';

    /**
     * Get the Service Manager of the Ricardo PHP lib
     *
     * @param int $website
     * @return Service
     */
    public function getServiceManager($website = 0)
    {
        if (!Mage::registry('ricardo_api_lang')) {
            Mage::register('ricardo_api_lang', Diglin_Ricento_Helper_Data::DEFAULT_SUPPORTED_LANG);
        }

        if (is_numeric($website)) {
            $storeId = Mage::app()->getWebsite($website)->getDefaultStore()->getId();
        } else if ($website instanceof Mage_Core_Model_Website) {
            $storeId = $website->getDefaultStore()->getId();
            $website = $website->getId();
        } else {
            $storeId = Mage_Core_Model_Store::DEFAULT_CODE;
        }

        $lang = Mage::registry('ricardo_api_lang');
        $registryKey = $this->_registryKey . ucwords($lang) . $website;

        if (!Mage::registry($registryKey)) {
            $helper = Mage::helper('diglin_ricento');

            if (!$helper->isConfigured()) {
                Mage::throwException($helper->__('Ricardo API Credentials are not configured. Please, configure the extension before to proceed.'));
            }

            if (!in_array($lang, $helper->getSupportedLang())) {
                Mage::throwException($helper->__('API language provided for the Service Manager is not supported.'));
            }

            if ($helper->isDevMode()) {
                $host = Mage::getStoreConfig(Diglin_Ricento_Helper_Data::CFG_API_HOST_DEV);
            } else {
                $host = Mage::getStoreConfig(Diglin_Ricento_Helper_Data::CFG_API_HOST);
            }

            $config = array(
                'host' => $host,
                'partnership_id' => $helper->getPartnerId($lang, $storeId),
                'partnership_passwd' => $helper->getPartnerPass($lang, $storeId),
                'partner_url' => $helper->getPartnerUrl($storeId),
                'allow_authorization_simulation' => ($helper->canSimulateAuthorization()) ? true : false,
                'customer_username' => $helper->getRicardoUsername($storeId),
                'customer_password' => $helper->getRicardoPass($storeId),
                'debug' => ($helper->isDebugEnabled($storeId)) ? true : false
            );

            Mage::register($registryKey, new Service(new Api(new Config($config))), false);
        }

        return Mage::registry($registryKey);
    }

    /**
     * Get last API call information
     *
     * @param int $website
     * @param bool $flush
     * @return array
     */
    public function getLastApiDebug($website, $flush = true)
    {
        return $this->getServiceManager($website)->getApi()->getLastDebug($flush);
    }
}