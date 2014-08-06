<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License BY-NC-ND.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material,
 * you may not distribute the modified material.
 * See the full license at http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * See http://mventory.com/legal/licensing/ for other licensing options.
 *
 * @package MVentory/TradeMe
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://creativecommons.org/licenses/by-nc-nd/4.0/
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */

/**
 * Resource serup model
 *
 * @package MVentory/TradeMe
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_TradeMe_Model_Resource_Setup
  extends Mage_Catalog_Model_Resource_Setup
{
  private $_fieldMap = array(
    //Fields from Mage_Eav_Model_Entity_Setup
    'backend' => 'backend_model',
    'type' => 'backend_type',
    'table' => 'backend_table',
    'frontend' => 'frontend_model',
    'input' => 'frontend_input',
    'label' => 'frontend_label',
    'frontend_class' => 'frontend_class',
    'source' => 'source_model',
    'required' => 'is_required',
    'user_defined' => 'is_user_defined',
    'default' => 'default_value',
    'unique' => 'is_unique',
    'note' => 'note',
    'global' => 'is_global',

    //Fields from Mage_Catalog_Model_Resource_Setup
    'input_renderer' => 'frontend_input_renderer',
    'visible' => 'is_visible',
    'searchable' => 'is_searchable',
    'filterable' => 'is_filterable',
    'comparable' => 'is_comparable',
    'visible_on_front' => 'is_visible_on_front',
    'wysiwyg_enabled' => 'is_wysiwyg_enabled',
    'is_html_allowed_on_front' => 'is_html_allowed_on_front',
    'visible_in_advanced_search' => 'is_visible_in_advanced_search',
    'filterable_in_search' => 'is_filterable_in_search',
    'used_in_product_listing' => 'used_in_product_listing',
    'used_for_sort_by' => 'used_for_sort_by',
    'apply_to' => 'apply_to',
    'position' => 'position',
    'is_configurable' => 'is_configurable',
    'used_for_promo_rules' => 'is_used_for_promo_rules'
  );

  public function addAttributes ($attrs) {
    $entityTypeId = $this->getEntityTypeId('catalog_product');
    $setId = $this->getDefaultAttributeSetId($entityTypeId);
    $groupId = $this->getDefaultAttributeGroupId($entityTypeId, $setId);

    foreach ($attrs as $name => $attr)
      $this
        ->addAttribute($entityTypeId, $name, $attr)
        ->addAttributeToGroup($entityTypeId, $setId, $groupId, $name);
  }

  public function updateAttributes ($attrs) {
    $entityTypeId = $this->getEntityTypeId('catalog_product');

    foreach ($attrs as $name => $attr)
      foreach ($attr as $field => $value)
        $this->updateAttribute(
          $entityTypeId,
          $name,
          isset($this->_fieldMap[$field]) ? $this->_fieldMap[$field] : $field,
          $value
        );
  }
}
