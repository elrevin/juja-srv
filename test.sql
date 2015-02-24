SELECT
  `news_tags`.`id`,
  `tags_tag`.`id`                                                                 AS `tag`,
  `tags_tag`.`title`                                                              AS `valof_tag`,
  IF((`news_tags`.`tag` IS NOT NULL AND `news_tags`.`master_table_id` = 1), 1, 0) AS `check`
FROM `news_tags`
  RIGHT JOIN `tags` ON `news_tags`.`tag` = `tags`.`id`
LIMIT 50