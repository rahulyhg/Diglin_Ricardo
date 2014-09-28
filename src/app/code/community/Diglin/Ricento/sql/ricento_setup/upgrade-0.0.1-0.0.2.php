<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @category    Diglin
 * @package     Diglin_Ricento
 * @copyright   Copyright (c) 2011-2015 Diglin (http://www.diglin.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$entityTypeId = $installer->getEntityTypeId(Mage_Catalog_Model_Category::ENTITY);
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'ricardo_category', array(
    'input_renderer'    => 'diglin_ricento/adminhtml_products_category_form_renderer_mapping',
    'type'              => 'int',
    'label'             => 'Ricardo Category',
    'note'              => 'Map this current Magento category with one of Ricardo. It will facilitate you the creation of product listing.',
    'input'             => 'text',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'user_defined'      => false,
    'unique'            => false,
    'default'           => ''
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'ricardo_category'
);