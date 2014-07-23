<?php

//Script loads matching rules from DB, converts ID of attribute set, attribute
//and attribute value to corresponding names and then prints converted data
//in JSON format on standard ouput

error_reporting(E_ALL | E_STRICT);

define('LF', PHP_EOL);

ini_set('display_errors', 1);

require 'app/Mage.php';

Mage::setIsDeveloperMode(true);

if (!Mage::isInstalled()) {
  echo "Application is not installed yet, please complete install wizard first.";
  exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

umask(0);

try {

$r = Mage::getSingleton('core/resource');

$sets = get_sets($r);
$attrs = get_attrs($r);
$options = get_options($r);
$rules = get_rules($r);

foreach ($rules as &$set) {
  if (isset($sets[$set['id']])) {
    $set['name'] = $sets[$set['id']];

    unset($set['id']);
  }

  $set['rules'] = unserialize($set['rules']);

  foreach ($set['rules'] as &$rule)
    foreach ($rule['attrs'] as &$attr) {
      if (isset($attrs[$attr['id']])) {
        $attr['name'] = $attrs[$attr['id']];

        unset($attr['id']);
      }

      foreach ($attr['value'] as $i => $value)
        if (isset($options[$value]))
          $attr['value'][$i] = $options[$value];
    }
}

echo json_encode($rules);

} catch (Exception $e) {
  Mage::printException($e);
}

function get_sets ($r) {
  $query = <<<'EOT'

SELECT attr_set.attribute_set_id
     , attr_set.attribute_set_name
FROM `%s` as attr_set
   , `%s` as entity_type
WHERE entity_type.entity_type_code = '%s'
  AND attr_set.entity_type_id = entity_type.entity_type_id
;

EOT;

  return $r
    ->getConnection('core_read')
    ->fetchPairs(sprintf(
        $query,
        $r->getTableName('eav/attribute_set'),
        $r->getTableName('eav/entity_type'),
        Mage_Catalog_Model_Product::ENTITY
      ));
}

function get_attrs ($r) {
  $query = <<<'EOT'

SELECT attr.attribute_id
     , attr.attribute_code
FROM `%s` as attr
   , `%s` as entity_type
WHERE entity_type.entity_type_code = '%s'
  AND attr.frontend_input IN ('select', 'multiselect')
  AND attr.entity_type_id = entity_type.entity_type_id
;

EOT;

  return $r
    ->getConnection('core_read')
    ->fetchPairs(sprintf(
        $query,
        $r->getTableName('eav/attribute'),
        $r->getTableName('eav/entity_type'),
        Mage_Catalog_Model_Product::ENTITY
      ));
}

function get_options ($r) {
  $query = <<<'EOT'

SELECT option_value.option_id
     , option_value.value
FROM `%s` as option_value
   , `%s` as attr_option
   , `%s` as attr
   , `%s` as entity_type
WHERE entity_type.entity_type_code = '%s'
  AND attr.frontend_input IN ('select', 'multiselect')
  AND attr.entity_type_id = entity_type.entity_type_id
  AND attr_option.attribute_id = attr.attribute_id
  AND attr_option.option_id = option_value.option_id
;

EOT;

  return $r
    ->getConnection('core_read')
    ->fetchPairs(sprintf(
        $query,
        $r->getTableName('eav/attribute_option_value'),
        $r->getTableName('eav/attribute_option'),
        $r->getTableName('eav/attribute'),
        $r->getTableName('eav/entity_type'),
        Mage_Catalog_Model_Product::ENTITY
      ));
}

function get_rules ($r) {
  $query = <<<'EOT'

SELECT attribute_set_id as id
     , rules
FROM `%s`
;

EOT;

  return $r
    ->getConnection('core_read')
    ->fetchAssoc(sprintf(
        $query,
        $r->getTableName('trademe/matching_rules')
      ));
}
