SELECT
  `catalog_goods_params`.`id`,
  `__link_model_table`.`id` AS `param`,
  `__link_model_table`.`title` AS `valof_param`,
  `catalog_goods_params`.`val_int` AS `val_int`,
  `catalog_goods_params`.`val_float` AS `val_float`,
  `catalog_goods_params`.`val_string` AS `val_string`,
  `catalog_goods_params`.`val_select` AS `val_select`,
  `catalog_params_values__1`.`title` AS `valof_val_select`,
  `catalog_goods_params`.`val_bool` AS `val_bool`,
  (__link_model_table.`type`) AS `type`,
  ('') AS `select_values`,
  (
 IF (
                    __link_model_table.type = 'string', catalog_goods_params.val_string, 
 IF (
                            __link_model_table.type = 'int', catalog_goods_params.val_int, 
 IF (
                                    __link_model_table.type = 'float', catalog_goods_params.val_float, 
 IF (
                                            __link_model_table.type = 'select', catalog_goods_params.val_select, 
 IF (
                                                    __link_model_table.type = 'bool', catalog_goods_params.val_bool, 
                                                    ''
                                            )
                                        )
                                )
                        )
                )
) AS `value`,
  IF((`catalog_goods_params`.`id` IS NOT NULL AND ``catalog_goods_params`.master_table_id` = 5), 1, 0) AS `check`
FROM `catalog_params` `__link_model_table` LEFT JOIN `catalog_goods_params` `catalog_goods_params`
    ON `__link_model_table`.`id` = `catalog_goods_params`.`param` AND `catalog_goods_params`.`master_table_id` = 5
  LEFT JOIN `catalog_params_values` `catalog_params_values__1`
    ON `catalog_goods_params`.`val_select` = `catalog_params_values__1`.id
WHERE `__link_model_table`.del = 0
ORDER BY `__link_model_table`.`id`
LIMIT 50