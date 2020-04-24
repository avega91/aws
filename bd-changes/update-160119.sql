TRUNCATE TABLE `archives`;
TRUNCATE TABLE `folder_apps`;
TRUNCATE TABLE `file_folders`;
ALTER TABLE `folder_apps` ADD `is_asset_folder` BOOLEAN NOT NULL DEFAULT FALSE AFTER `allow_assets`;