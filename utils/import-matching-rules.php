<?php

//Script reads matching rules in JSON format from the specified in first
//parameter file, tries to convert name of attribute set, attribute
//and attribute value to ID and then saves converted data in DB

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

$rules = json_decode(file_get_contents($argv[1]), true);

$r = Mage::getSingleton('core/resource');

$sets = get_sets($r);
$attrs = get_attrs($r);
$options = get_options($r);

foreach ($rules as &$set) {
  if (!isset($sets[$set['name']]))
    throw new Exception( $set['name'] . ' atrribute set doesn\'t exist');

  $set['id'] = $sets[$set['name']];
  unset($set['name']);

  foreach ($set['rules'] as &$rule)
    foreach ($rule['attrs'] as &$attr) {
      if (!isset($attrs[$attr['name']]))
        throw new Exception($attr['name'] . ' atrribute doesn\'t exist');

      $attr['id'] = $attrs[$attr['name']];

      foreach ($attr['value'] as $i => $value)
        if (isset($options[$value]))
          $attr['value'][$i] = $options[$value];
        else
          throw new Exception(
            $value . ' value in ' . $attr['name'] . ' attribuet doesn\'t exist'
          );

      unset($attr['name']);
    }

  $set['rules'] = serialize($set['rules']);
}

save_rules($r, $rules);

} catch (Exception $e) {
  echo $e->getMessage(), LF;
}

function get_sets ($r) {
  $query = <<<'EOT'

SELECT attr_set.attribute_set_name
     , attr_set.attribute_set_id
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

SELECT attr.attribute_code
     , attr.attribute_id
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

SELECT option_value.value
     , option_value.option_id
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

function save_rules ($r, $rules) {
  $table = $r->getTableName('trademe/matching_rules');
  $c = $r->getConnection('core_write');

  $c->truncateTable($table);

  $c->beginTransaction();

  foreach ($rules as $set)
    $c->insert(
      $table,
      array('attribute_set_id' => $set['id'], 'rules' => $set['rules'])
    );

  $c->commit();
}
