<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @category    Diglin
 * @package     Diglin_Ricento
 * @copyright   Copyright (c) 2011-2014 Diglin (http://www.diglin.com)
 */
class Diglin_Ricento_Block_Adminhtml_Products_Listing_Item_Edit_Form extends Diglin_Ricento_Block_Adminhtml_Products_Listing_Form_Abstract
{
    public function isReadonlyForm()
    {
        foreach ($this->getSelectedItems() as $item) {
            /* @var $item Diglin_Ricento_Model_Products_Listing_Item */
            if ($item->getStatus() === Diglin_Ricento_Helper_Data::STATUS_LISTED) {
                return true;
            }
        }
        return false;
    }
    public function getReadonlyNote()
    {
        return $this->__('Listed items cannot be modified. Stop the listing first to make changes.');
    }
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ricento/products/listing/item/edit/form.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('tabs',
            $this->getLayout()->createBlock('diglin_ricento/adminhtml_products_listing_item_edit_tabs', 'tabs')
        );
        return parent::_prepareLayout();
    }
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form' ,
            'action' => $this->getUrl('*/*/save', array(
                    'id' => $this->getRequest()
                            ->getParam('id')
                )) , 'method' => 'post' , 'enctype' => 'multipart/form-data'
        ));
        $form->addField('item_ids', 'hidden', array(
            'name'    => 'item_ids'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabsHtml()
    {
        return $this->getChildHtml('tabs');
    }

    protected function _initFormValues()
    {
        parent::_initFormValues();
        $this->getForm()->addValues(array('item_ids' => join(',', $this->getSelectedItems()->getAllIds())));
        return $this;
    }

    /**
     * Returns items that are selected to be configured
     *
     * @return Diglin_Ricento_Model_Resource_Products_Listing_Item_Collection
     */
    public function getSelectedItems()
    {
        return Mage::registry('selected_items');
    }
}