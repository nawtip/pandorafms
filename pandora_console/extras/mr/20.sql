START TRANSACTION;

ALTER TABLE treport_content ADD COLUMN `recursion` TINYINT(1) default NULL;

ALTER TABLE tevent_filter ADD COLUMN `user_comment` text NOT NULL;
ALTER TABLE tevent_filter ADD COLUMN `source` tinytext NOT NULL;
ALTER TABLE tevent_filter ADD COLUMN `id_extra` tinytext NOT NULL;

ALTER TABLE tagente_modulo ALTER COLUMN `parent_module_id` SET default 0;

COMMIT;