-- Basic Database for ACLayer
-- Generation Time: 2011-02-06 11:44
-- MySQL version: 5.1.41

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `acl_map_permissions` (
  `aclmpkey` bigint(14) unsigned NOT NULL AUTO_INCREMENT,
  `permkey` varchar(200) DEFAULT NULL,
  `permscope` int(2) DEFAULT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  `roleid` bigint(8) DEFAULT NULL,
  `groupid` bigint(8) DEFAULT NULL,
  `permval` tinyint(1) NOT NULL DEFAULT '0',
  `restypeid` bigint(2) DEFAULT NULL,
  `resid` varchar(100) DEFAULT NULL,
  `grantby` bigint(2) DEFAULT NULL,
  `grantfromid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`aclmpkey`),
  KEY `permkey` (`permkey`),
  KEY `uid` (`uid`),
  KEY `roleid` (`roleid`),
  KEY `groupid` (`groupid`),
  KEY `restypeid` (`restypeid`),
  KEY `grantfromid` (`grantfromid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 ;

INSERT INTO `acl_map_permissions` (`aclmpkey`, `permkey`, `permscope`, `uid`, `roleid`, `groupid`, `permval`, `restypeid`, `resid`, `grantby`, `grantfromid`) VALUES
(1, 'admin', 3, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL),
(2, 'group', 3, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL);

CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `permkey` varchar(200) NOT NULL,
  `parentpermkey` varchar(200) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`permkey`),
  KEY `parentpermkey` (`parentpermkey`)
) ENGINE=InnoDB;

INSERT INTO `acl_permissions` (`permkey`, `parentpermkey`, `name`, `description`) VALUES
('admin', NULL, 'Administration', 'Top of Administration permissions'),
('admin_role', 'admin', 'Role', 'Role Administration'),
('admin_role_create', 'admin_role', 'Create', 'Create new roles'),
('admin_role_edit', 'admin_role', 'Edit', 'Modify role information'),
('admin_role_grant', 'admin_role', 'Grant', 'Grant access to roles'),
('admin_role_permadd', 'admin_role', 'Add Permission', 'Grant permissions to role'),
('admin_role_permrem', 'admin_role', 'Remove Permission', 'Revoke permission from role'),
('admin_role_revoke', 'admin_role', 'Revoke', 'Revoke access to role'),
('group', NULL, 'Group', 'Manage Groups'),
('group_create', 'group', 'Create', 'Create new groups'),
('group_useradd', 'group', 'Add User', 'Add user to group'),
('group_userrem', 'group', 'Remove Permission', 'Remove user from group');

CREATE TABLE IF NOT EXISTS `acl_ref_permscope` (
  `permscope` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`permscope`)
) ENGINE=InnoDB AUTO_INCREMENT=6 ;

INSERT INTO `acl_ref_permscope` (`permscope`, `name`) VALUES
(1, 'Self'),
(2, 'Group'),
(3, 'All'),
(4, 'User'),
(5, 'Resource');

CREATE TABLE IF NOT EXISTS `acl_ref_restypes` (
  `restypeid` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`restypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 ;

INSERT INTO `acl_ref_restypes` (`restypeid`, `name`) VALUES
(1, 'Group'),
(2, 'Project'),
(3, 'Task');

CREATE TABLE IF NOT EXISTS `acl_resources` (
  `resid` varchar(100) NOT NULL,
  `owneruid` bigint(8) NOT NULL,
  `restypeid` int(2) NOT NULL,
  PRIMARY KEY (`resid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `groups` (
  `groupid` bigint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `creatoruid` bigint(8) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `roles` (
  `roleid` bigint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 ;

INSERT INTO `users` (`uid`, `name`) VALUES (1, 'Root User');

ALTER TABLE `acl_map_permissions`
  ADD CONSTRAINT `acl_map_permissions_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `acl_map_permissions_ibfk_2` FOREIGN KEY (`grantfromid`) REFERENCES `users` (`uid`);