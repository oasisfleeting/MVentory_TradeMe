#Script allows to match product's category to the value of the specified
#attribute and store matched value in product

#Parameters
set @store_code = 'admin';
set @type_attr_name = 'product_type';

#Leave empty to process products from all categories
#Set path like 'a/b' to process all products from category 'b' and all of its
#subcategories
#Path starts from ID of root category because Magento can contain several root
#categories.
#
#Example:
#
# - 2:Default Category
#   - 3:Category
#     - 5:Subcategory
#   - 4:Another category
#
#'2/3/5' - process products only from 'Subcategory'
#'2/4' - process products only from 'Another category'
#'2/3' - process products both from 'Category' and 'Subcategory'
set @category_filter_path = '2/5/29';

#Constants
set @CATEGORY_TYPE_ID = 3;
set @PRODUCT_TYPE_ID = 4;

#Temp variables
set @category_filter_path = trim('/' from @category_filter_path);
set @category_filter_path = if (
  @category_filter_path,
  concat('1/', trim('/' from @category_filter_path), '%'),
  '1%'
);

set @store_id = (
  select store_id
  from core_store
  where code = @store_code
  limit 1
);

set @type_attr_id = (
  select attribute_id
  from eav_attribute
  where entity_type_id = @PRODUCT_TYPE_ID
    and attribute_code = @type_attr_name
  limit 1
);

set @type_attr_table = (
  select concat('catalog_product_entity_', backend_type)
  from eav_attribute
  where attribute_id = @type_attr_id
  limit 1
);

set @name_attr_id = (
  select attribute_id
  from eav_attribute
  where entity_type_id = @CATEGORY_TYPE_ID
    and attribute_code = 'name'
  limit 1
);

set @query = concat('
insert into ', @type_attr_table, '
  (entity_type_id, attribute_id, store_id, entity_id, value)
select @PRODUCT_TYPE_ID
     , @type_attr_id
     , @store_id
     , p.entity_id
     , ov.option_id
from catalog_product_entity as p
   , catalog_category_entity as c
   , catalog_category_product as cp
   , catalog_category_entity_varchar as cn
   , eav_attribute_option as o
   , eav_attribute_option_value as ov
where c.path like @category_filter_path
  and cn.attribute_id = @name_attr_id
  and o.attribute_id = @type_attr_id
  and ov.store_id = @store_id
  and p.entity_id = cp.product_id
  and c.entity_id = cp.category_id
  and cn.entity_id = cp.category_id
  and o.option_id = ov.option_id
  and cn.value = ov.value
on duplicate key update value = values(value)
');

prepare stmt from @query;
execute stmt;
deallocate prepare stmt;
