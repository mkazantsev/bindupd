CREATE DATABASE  `bindupd`;

CREATE TABLE  `bindupd`.`User_Type` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` VARCHAR( 256 ) NOT NULL
);

INSERT INTO  `bindupd`.`User_Type` (`id` ,`name`) VALUES (1,  'user');
INSERT INTO  `bindupd`.`User_Type` (`id` ,`name`) VALUES (2,  'admin');

CREATE TABLE  `bindupd`.`User_State` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` VARCHAR( 256 ) NOT NULL
);

INSERT INTO  `bindupd`.`User_State` (`id` ,`name`) VALUES (1,  'enabled');
INSERT INTO  `bindupd`.`User_State` (`id` ,`name`) VALUES (2,  'disabled');

CREATE TABLE  `bindupd`.`Operation_Type` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` VARCHAR( 256 ) NOT NULL
);

INSERT INTO  `bindupd`.`Operation_Type` (`id` ,`name`) VALUES (1,  'add');
INSERT INTO  `bindupd`.`Operation_Type` (`id` ,`name`) VALUES (2,  'edit');
INSERT INTO  `bindupd`.`Operation_Type` (`id` ,`name`) VALUES (3,  'delete');
INSERT INTO  `bindupd`.`Operation_Type` (`id` ,`name`) VALUES (4,  'view');

CREATE TABLE `bindupd`.`User` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR( 256 ) NOT NULL,
	`password_hash` VARCHAR( 256 ) NOT NULL,
	`state_id` INT NOT NULL,
	`type_id` INT NOT NULL,
	FOREIGN KEY (`state_id`) REFERENCES `bindupd`.`User_State`(`id`),
	FOREIGN KEY (`type_id`) REFERENCES `bindupd`.`User_Type`(`id`)
);

CREATE TABLE `bindupd`.`Operation` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`type_id` INT NOT NULL,
	`user_id` INT NOT NULL,
	`date` DATE NOT NULL,
	`old_value` VARCHAR ( 256 ) NOT NULL,
	`new_value` VARCHAR ( 256 ) NOT NULL,
	FOREIGN KEY (`type_id`) REFERENCES `bindupd`.`Operation_Type`(`id`),
	FOREIGN KEY (`user_id`) REFERENCES `bindupd`.`User`(`id`)
);