<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

$SQL[] = "CREATE TABLE `". $db_prefix ."articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `content` longtext NOT NULL,
  `html` tinyint(1) NOT NULL DEFAULT '0',
  `rating_average` float NOT NULL DEFAULT '0',
  `rating_total` float NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rating` tinyint(1) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `keywords` text NOT NULL,
  `modified` int(10) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `full_index` (`title`,`description`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."article_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `html` tinyint(4) NOT NULL DEFAULT '0',
  `staff` tinyint(4) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."article_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `rating` float NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."asessions` (
  `s_id` varchar(40) NOT NULL,
  `s_uid` int(11) NOT NULL DEFAULT '0',
  `s_uname` varchar(255) NOT NULL,
  `s_location` varchar(255) NOT NULL,
  `s_inticket` int(11) NOT NULL DEFAULT '0',
  `s_messages` text NOT NULL,
  `s_time` int(10) NOT NULL DEFAULT '0',
  `s_ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`s_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$SQL[] = "CREATE TABLE `". $db_prefix ."assign_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` varchar(20) NOT NULL,
  `content_id` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `real_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `articles` int(11) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rating` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `assign_auto` text NOT NULL,
  `assign_move` tinyint(1) NOT NULL DEFAULT '0',
  `assign_move_clear_in` tinyint(1) NOT NULL DEFAULT '0',
  `assign_move_clear_out` tinyint(1) NOT NULL DEFAULT '0',
  `close_own` tinyint(1) NOT NULL DEFAULT '0',
  `reopen_own` tinyint(1) NOT NULL DEFAULT '0',
  `escalate_enable` tinyint(1) NOT NULL DEFAULT '0',
  `escalate_user` tinyint(1) NOT NULL DEFAULT '0',
  `escalate_depart` int(11) NOT NULL DEFAULT '0',
  `escalate_wait` int(11) NOT NULL DEFAULT '0',
  `escalate_assign` text NOT NULL,
  `escalate_assign_clear_in` tinyint(1) NOT NULL DEFAULT '0',
  `escalate_assign_clear_out` tinyint(1) NOT NULL DEFAULT '0',
  `close_auto` int(11) NOT NULL DEFAULT '0',
  `allow_attach` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rating` tinyint(1) NOT NULL DEFAULT '0',
  `tickets_total` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."depart_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(25) NOT NULL,
  `extra` text NOT NULL,
  `departs` text NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."depart_fields_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `extra` varchar(225) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."flags_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `fid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."groups` (
  `g_id` int(11) NOT NULL AUTO_INCREMENT,
  `g_name` varchar(255) NOT NULL,
  `g_ticket_access` tinyint(1) NOT NULL DEFAULT '0',
  `g_ticket_create` tinyint(1) NOT NULL DEFAULT '0',
  `g_ticket_edit` tinyint(1) NOT NULL DEFAULT '0',
  `g_ticket_escalate` tinyint(1) NOT NULL DEFAULT '0',
  `g_ticket_close` tinyint(1) NOT NULL DEFAULT '0',
  `g_ticket_reopen` tinyint(1) NOT NULL DEFAULT '0',
  `g_ticket_attach` tinyint(1) NOT NULL DEFAULT '0',
  `g_reply_edit` tinyint(1) NOT NULL DEFAULT '0',
  `g_reply_delete` tinyint(1) NOT NULL DEFAULT '0',
  `g_reply_rate` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_access` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_comment` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_com_edit` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_com_delete` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_com_edit_all` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_com_delete_all` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_rate` tinyint(1) NOT NULL DEFAULT '0',
  `g_kb_perm` text NOT NULL,
  `g_news_comment` tinyint(1) NOT NULL DEFAULT '0',
  `g_news_com_edit` tinyint(1) NOT NULL DEFAULT '0',
  `g_news_com_delete` tinyint(1) NOT NULL DEFAULT '0',
  `g_news_com_edit_all` tinyint(1) NOT NULL DEFAULT '0',
  `g_news_com_delete_all` tinyint(1) NOT NULL DEFAULT '0',
  `g_change_skin` tinyint(1) NOT NULL DEFAULT '0',
  `g_change_lang` tinyint(1) NOT NULL DEFAULT '0',
  `g_assign_outside` tinyint(1) NOT NULL DEFAULT '0',
  `g_hide_names` tinyint(1) NOT NULL DEFAULT '0',
  `g_upload_size_max` int(11) NOT NULL DEFAULT '0',
  `g_upload_exts` text NOT NULL,
  `g_depart_perm` text NOT NULL,
  `g_acp_access` tinyint(1) NOT NULL DEFAULT '0',
  `g_acp_perm` text NOT NULL,
  `g_acp_depart_perm` text NOT NULL,
  `g_users` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`g_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `users` int(11) NOT NULL DEFAULT '0',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `action` text NOT NULL,
  `extra` text NOT NULL,
  `type` varchar(20) NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT '0',
  `content_type` varchar(20) NOT NULL,
  `content_id` int(11) NOT NULL DEFAULT '0',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `excerpt` text NOT NULL,
  `content` text NOT NULL,
  `html` tinyint(4) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."news_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `html` tinyint(1) NOT NULL DEFAULT '0',
  `staff` tinyint(1) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `modified` int(10) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."priorities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon_regular` varchar(255) NOT NULL,
  `icon_assigned` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."profile_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(25) NOT NULL,
  `extra` text NOT NULL,
  `ticket` tinyint(1) NOT NULL DEFAULT '0',
  `staff` tinyint(1) NOT NULL DEFAULT '0',
  `perms` text NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."profile_fields_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `extra` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `html` tinyint(1) NOT NULL DEFAULT '0',
  `signature` tinyint(1) NOT NULL DEFAULT '0',
  `staff` tinyint(1) NOT NULL DEFAULT '0',
  `secret` tinyint(1) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."reply_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."reply_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `content_html` longtext NOT NULL,
  `content_plaintext` longtext NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."sessions` (
  `s_id` varchar(40) NOT NULL,
  `s_uid` int(11) NOT NULL DEFAULT '0',
  `s_uname` varchar(255) NOT NULL,
  `s_email` varchar(255) NOT NULL,
  `s_location` text NOT NULL,
  `s_tkey` varchar(255) NOT NULL,
  `s_guest` tinyint(1) NOT NULL DEFAULT '0',
  `s_time` int(10) NOT NULL DEFAULT '0',
  `s_ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`s_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."settings` (
  `cf_id` int(11) NOT NULL AUTO_INCREMENT,
  `cf_key` varchar(255) NOT NULL,
  `cf_group` varchar(255) NOT NULL,
  `cf_type` varchar(255) NOT NULL,
  `cf_extra` text NOT NULL,
  `cf_value` text NOT NULL,
  `cf_value_old` text NOT NULL,
  `cf_default` text NOT NULL,
  `cf_callback` tinyint(1) NOT NULL DEFAULT '0',
  `cf_position` int(11) NOT NULL DEFAULT '0',
  `cf_cache` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cf_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."settings_groups` (
  `cg_id` int(11) NOT NULL AUTO_INCREMENT,
  `cg_key` varchar(255) NOT NULL,
  `cg_set_count` int(11) NOT NULL DEFAULT '0',
  `cg_hide` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."skins` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `users` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_staff` varchar(255) NOT NULL,
  `name_user` varchar(255) NOT NULL,
  `abbr_staff` varchar(255) NOT NULL,
  `abbr_user` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mask` varchar(255) NOT NULL,
  `did` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `html` tinyint(1) NOT NULL DEFAULT '0',
  `last_uid` int(11) NOT NULL DEFAULT '0',
  `replies` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `rating` float NOT NULL DEFAULT '0',
  `rating_total` float NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  `close_uid` int(11) NOT NULL DEFAULT '0',
  `close_date` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `accepted` tinyint(1) NOT NULL DEFAULT '0',
  `aua` tinyint(1) NOT NULL DEFAULT '0',
  `escalated` tinyint(1) NOT NULL DEFAULT '0',
  `onhold` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `allow_reopen` tinyint(1) NOT NULL DEFAULT '0',
  `last_reply` int(10) NOT NULL DEFAULT '0',
  `last_reply_staff` int(10) NOT NULL DEFAULT '0',
  `last_reply_all` int(10) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mask` (`mask`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."tickets_guests` (
  `id` int(11) NOT NULL DEFAULT '0',
  `gname` varchar(255) NOT NULL,
  `key` varchar(10) NOT NULL,
  `lang` int(11) NOT NULL DEFAULT '0',
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."tickets_track` (
  `tid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."upg_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verid` int(11) NOT NULL DEFAULT '0',
  `verhuman` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `ukey` varchar(255) NOT NULL,
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `pass_salt` varchar(255) NOT NULL,
  `login_key` varchar(255) NOT NULL,
  `ugroup` int(11) NOT NULL DEFAULT '0',
  `ugroup_sub` varchar(255) NOT NULL,
  `ugroup_sub_acp` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `signature` text NOT NULL,
  `sig_html` tinyint(1) NOT NULL DEFAULT '0',
  `sig_auto` tinyint(1) NOT NULL DEFAULT '0',
  `time_zone` varchar(5) NOT NULL,
  `time_dst` tinyint(1) NOT NULL DEFAULT '0',
  `rte_enable` tinyint(1) NOT NULL DEFAULT '0',
  `email_enable` tinyint(1) NOT NULL DEFAULT '0',
  `email_ticket` tinyint(1) NOT NULL DEFAULT '0',
  `email_action` tinyint(1) NOT NULL DEFAULT '0',
  `email_news` tinyint(1) NOT NULL DEFAULT '0',
  `email_type` tinyint(1) NOT NULL DEFAULT '0',
  `rss_key` varchar(255) NOT NULL,
  `lang` int(11) NOT NULL,
  `skin` int(11) NOT NULL,
  `tickets_total` int(11) NOT NULL DEFAULT '0',
  `tickets_open` int(11) NOT NULL DEFAULT '0',
  `val_email` tinyint(1) NOT NULL DEFAULT '0',
  `val_admin` tinyint(1) NOT NULL DEFAULT '0',
  `joined` int(10) NOT NULL,
  `ipadd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."users_staff` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `email_staff_enable` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_user_approve` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_ticket` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_reply` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_assign` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_escalate` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_hold` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_move_to` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_move_away` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_close` tinyint(1) NOT NULL DEFAULT '0',
  `email_staff_reopen` tinyint(1) NOT NULL DEFAULT '0',
  `esn_unassigned` tinyint(1) NOT NULL DEFAULT '0',
  `esn_assigned` tinyint(1) NOT NULL DEFAULT '0',
  `esn_assigned_to_me` tinyint(1) NOT NULL DEFAULT '0',
  `columns_tm` text NOT NULL,
  `sort_tm` varchar(255) NOT NULL,
  `order_tm` tinyint(1) NOT NULL DEFAULT '0',
  `dfilters_status` text NOT NULL,
  `dfilters_depart` text NOT NULL,
  `dfilters_priority` text NOT NULL,
  `dfilters_flag` text NOT NULL,
  `dfilters_assigned` tinyint(1) NOT NULL DEFAULT '0',
  `auto_assign` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "CREATE TABLE `". $db_prefix ."validation` (
  `id` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$SQL[] = "INSERT INTO `". $db_prefix ."articles` VALUES(1, 1, 'Sample Article', 'This is a sample article that can be edited or deleted at any time.', '&lt;p&gt;This is a sample article that can be edited or deleted at any time.&lt;/p&gt;', 1, 0, 0, 1, 1, 0, 0, 0, '', 0, ". mysql_real_escape_string( intval( time() ) ) .");";

$SQL[] = "INSERT INTO `". $db_prefix ."categories` VALUES(1, 0, 'Sample Category', 'This is a sample category that can be edited or deleted at any time.', 0, 1, 1, 0);";

$SQL[] = "INSERT INTO `". $db_prefix ."departments` VALUES(1, 'Sample Department', 'This is a sample department that can be edited or deleted at any time.', 'N;', 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N;', 0, 0, 0, 1, 0, 0, 1);";

# TODO: select default permissions

$SQL[] = "INSERT INTO `". $db_prefix ."groups` VALUES(1, 'Members', 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0, 1, 'a:1:{i:1;i:1;}', 1, 1, 0, 0, 0, 1, 1, 0, 0, 2048, '', 'a:1:{i:1;i:1;}', 0, 'a:0:{}', 'a:12:{i:2;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:3;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:4;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:15;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:5;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:6;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:7;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:12;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:11;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:14;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:1;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:9;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}}', 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."groups` VALUES(2, 'Guests', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 'a:1:{i:1;i:1;}', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 'a:1:{i:1;i:1;}', 0, 'a:0:{}', 'a:12:{i:2;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:3;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:4;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:15;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:5;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:6;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:7;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:12;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:11;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:14;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:1;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:9;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}}', 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."groups` VALUES(3, 'Validating', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 'a:1:{i:1;i:1;}', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 'a:1:{i:1;i:1;}', 0, 'N;', 'a:12:{i:2;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:3;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:4;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:15;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:5;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:6;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:7;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:12;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:11;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:14;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:9;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:1;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}}', 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."groups` VALUES(4, 'Administrators', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'a:1:{i:1;i:1;}', 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 'doc,docx,xls,xlsx,txt,pdf,zip,gz,rar,tar,gif,jpg,jpeg,png,tiff', 'a:1:{i:1;i:1;}', 1, 'a:81:{s:17:\"manage_priorities\";i:1;s:21:\"manage_priorities_add\";i:1;s:22:\"manage_priorities_edit\";i:1;s:24:\"manage_priorities_delete\";i:1;s:25:\"manage_priorities_reorder\";i:1;s:15:\"manage_statuses\";i:1;s:19:\"manage_statuses_add\";i:1;s:20:\"manage_statuses_edit\";i:1;s:22:\"manage_statuses_delete\";i:1;s:23:\"manage_statuses_reorder\";i:1;s:12:\"manage_flags\";i:1;s:16:\"manage_flags_add\";i:1;s:17:\"manage_flags_edit\";i:1;s:19:\"manage_flags_delete\";i:1;s:20:\"manage_flags_reorder\";i:1;s:17:\"manage_rtemplates\";i:1;s:21:\"manage_rtemplates_add\";i:1;s:22:\"manage_rtemplates_edit\";i:1;s:24:\"manage_rtemplates_delete\";i:1;s:25:\"manage_rtemplates_reorder\";i:1;s:14:\"manage_departs\";i:1;s:18:\"manage_departs_add\";i:1;s:19:\"manage_departs_edit\";i:1;s:21:\"manage_departs_delete\";i:1;s:22:\"manage_departs_reorder\";i:1;s:15:\"manage_cdfields\";i:1;s:19:\"manage_cdfields_add\";i:1;s:20:\"manage_cdfields_edit\";i:1;s:22:\"manage_cdfields_delete\";i:1;s:23:\"manage_cdfields_reorder\";i:1;s:12:\"manage_users\";i:1;s:16:\"manage_users_add\";i:1;s:17:\"manage_users_edit\";i:1;s:19:\"manage_users_delete\";i:1;s:20:\"manage_users_approve\";i:1;s:13:\"manage_groups\";i:1;s:17:\"manage_groups_add\";i:1;s:18:\"manage_groups_edit\";i:1;s:20:\"manage_groups_delete\";i:1;s:15:\"manage_cpfields\";i:1;s:19:\"manage_cpfields_add\";i:1;s:20:\"manage_cpfields_edit\";i:1;s:22:\"manage_cpfields_delete\";i:1;s:23:\"manage_cpfields_reorder\";i:1;s:11:\"manage_news\";i:1;s:15:\"manage_news_add\";i:1;s:16:\"manage_news_edit\";i:1;s:18:\"manage_news_delete\";i:1;s:15:\"manage_articles\";i:1;s:19:\"manage_articles_add\";i:1;s:20:\"manage_articles_edit\";i:1;s:22:\"manage_articles_delete\";i:1;s:17:\"manage_categories\";i:1;s:21:\"manage_categories_add\";i:1;s:22:\"manage_categories_edit\";i:1;s:24:\"manage_categories_delete\";i:1;s:25:\"manage_categories_reorder\";i:1;s:12:\"manage_pages\";i:1;s:16:\"manage_pages_add\";i:1;s:17:\"manage_pages_edit\";i:1;s:19:\"manage_pages_delete\";i:1;s:10:\"look_skins\";i:1;s:14:\"look_skins_add\";i:1;s:15:\"look_skins_edit\";i:1;s:17:\"look_skins_delete\";i:1;s:16:\"look_skins_tools\";i:1;s:10:\"look_langs\";i:1;s:14:\"look_langs_add\";i:1;s:15:\"look_langs_edit\";i:1;s:17:\"look_langs_delete\";i:1;s:16:\"look_langs_tools\";i:1;s:11:\"look_emails\";i:1;s:14:\"tools_settings\";i:1;s:11:\"tools_maint\";i:1;s:19:\"tools_maint_recount\";i:1;s:19:\"tools_maint_rebuild\";i:1;s:17:\"tools_maint_clean\";i:1;s:13:\"tools_backups\";i:1;s:20:\"tools_backups_backup\";i:1;s:21:\"tools_backups_restore\";i:1;s:10:\"tools_logs\";i:1;}', 'a:14:{i:2;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:3;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:4;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:15;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:5;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:1;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:6;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:7;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:12;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:11;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:14;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:1;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:9;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:16;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:17;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}}', 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."groups` VALUES(5, 'Staff', 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 'a:1:{i:1;i:1;}', 1, 1, 1, 1, 0, 1, 1, 0, 0, 0, '', 'a:1:{i:1;i:1;}', 1, 'a:9:{s:12:\"manage_users\";i:1;s:16:\"manage_users_add\";i:1;s:17:\"manage_users_edit\";i:1;s:15:\"manage_articles\";i:1;s:19:\"manage_articles_add\";i:1;s:20:\"manage_articles_edit\";i:1;s:11:\"tools_maint\";i:1;s:19:\"tools_maint_recount\";i:1;s:19:\"tools_maint_rebuild\";i:1;}', 'a:14:{i:2;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:0;s:2:\"ro\";i:1;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:3;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:1;s:2:\"et\";i:0;s:2:\"er\";i:1;s:2:\"mv\";i:0;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:0;s:1:\"c\";i:1;s:2:\"ro\";i:0;s:2:\"dt\";i:1;s:2:\"dr\";i:0;}i:4;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:0;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:0;}i:15;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:0;s:2:\"et\";i:1;s:2:\"er\";i:0;s:2:\"mv\";i:1;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:1;s:1:\"c\";i:0;s:2:\"ro\";i:1;s:2:\"dt\";i:0;s:2:\"dr\";i:1;}i:5;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:1;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:6;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:7;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:12;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:1;s:2:\"et\";i:1;s:2:\"er\";i:1;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:11;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:14;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:1;a:12:{s:1:\"v\";i:1;s:1:\"r\";i:0;s:2:\"et\";i:1;s:2:\"er\";i:0;s:2:\"mv\";i:1;s:2:\"es\";i:1;s:2:\"as\";i:1;s:2:\"aa\";i:1;s:1:\"c\";i:1;s:2:\"ro\";i:1;s:2:\"dt\";i:1;s:2:\"dr\";i:1;}i:9;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:16;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}i:17;a:12:{s:1:\"v\";i:0;s:1:\"r\";i:0;s:2:\"et\";i:0;s:2:\"er\";i:0;s:2:\"mv\";i:0;s:2:\"es\";i:0;s:2:\"as\";i:0;s:2:\"aa\";i:0;s:1:\"c\";i:0;s:2:\"ro\";i:0;s:2:\"dt\";i:0;s:2:\"dr\";i:0;}}', 0);";

$SQL[] = "INSERT INTO `". $db_prefix ."languages` VALUES(1, 'en', 'English', 1, 1);";

$SQL[] = "INSERT INTO `". $db_prefix ."news` VALUES(1, 1, 'Sample News Item', 'This is a sample news item that can be edited or deleted at any time.', '&lt;p&gt;This is a sample news item that can be edited or deleted at any time.&lt;/p&gt;', 1, 0, 0, 0, ". mysql_real_escape_string( intval( time() ) ) .", '". mysql_real_escape_string( $this->trellis->input['ip_address'] ) ."');";

$SQL[] = "INSERT INTO `". $db_prefix ."priorities` VALUES(1, 'Low', 'priority_blue.gif', 'priority_blue_dot.gif', 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."priorities` VALUES(2, 'Medium', 'priority_yellow.gif', 'priority_yellow_dot.gif', 0, 2);";
$SQL[] = "INSERT INTO `". $db_prefix ."priorities` VALUES(3, 'High', 'priority_orange.gif', 'priority_orange_dot.gif', 0, 3);";
$SQL[] = "INSERT INTO `". $db_prefix ."priorities` VALUES(4, 'Urgent', 'priority_red.gif', 'priority_red_dot.gif', 0, 4);";

$SQL[] = "INSERT INTO `". $db_prefix ."reply_templates` VALUES(1, 'Sample Canned Reply', 'This is a sample canned reply that can be edited or deleted at any time.', 'This is a &lt;strong&gt;sample&lt;/strong&gt; canned reply that can be edited or deleted at any time.', 'This is a sample canned reply that can be edited or deleted at any time.', 1);";

# TODO: primary_key id to null so that these are numbered correctly
# TODO: get correct position / order values

$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(1, 'hd_name', 'general', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['hd_name'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['hd_name'] ) ."', 'Trellis Desk', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(2, 'dashboard_amount', 'news', 'textfield', '', '3', '', '3', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(3, 'sidebar_recent_count', 'kb', 'textfield', '', '5', '', '5', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(4, 'sidebar_views_count', 'kb', 'textfield', '', '5', '', '5', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(5, 'registration', 'security', 'yes_no', '', '1', '', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(6, 'validation_email', 'security', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['validation_email'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['validation_email'] ) ."', '1', 1, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(7, 'session_timeout', 'security', 'textfield', '', '20', '', '20', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(8, 'shutdown_enable', 'general', 'yes_no', '', '1', '0', '1', 0, 11, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(10, 'new_tickets', 'ticket', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(29, 'rte', 'ticket', 'yes_no', '', '1', '', '1', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(12, 'enable', 'kb', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(13, 'rating', 'kb', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['kb_rating'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['kb_rating'] ) ."', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(14, 'comments', 'kb', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['kb_comments'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['kb_comments'] ) ."', '1', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(16, 'emails', 'ban', 'textarea', '', '', '', '', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(17, 'ips', 'ban', 'textarea', '', '', '', '', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(18, 'rating', 'ticket', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['reply_rating'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['reply_rating'] ) ."', '1', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(19, 'change_skin', 'look', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(20, 'change_lang', 'look', 'yes_no', '', '1', '', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(21, 'validation_admin', 'security', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['validation_admin'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['validation_admin'] ) ."', '0', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(22, 'out_address', 'email', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['out_address'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['out_address'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['out_address'] ) ."', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(91, 'enable', 'antispam', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['enable'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['enable'] ) ."', '0', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(24, 'rte', 'kb', 'yes_no', '', '1', '1', '1', 0, 11, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(62, 'tformat_short', 'general', 'textfield', '', 'n/j/y, g:i A', '', 'n/j/y, g:i A', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(26, 'session_ip_check', 'security', 'yes_no', '', '0', '', '0', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(72, 'reply', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(71, 'ticket', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(30, 'attachments', 'ticket', 'yes_no', '', '1', '', '1', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(31, 'upload_dir', 'general', 'textfield', '', '". mysql_real_escape_string( $upload_path ) ."', '". mysql_real_escape_string( $upload_path ) ."', '". mysql_real_escape_string( $upload_path ) ."', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(111, 'shutdown_time', 'general', 'textfield', '', '30', '24', '30', 0, 12, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(112, 'enable', 'log', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(113, 'acp', 'log', 'yes_no', '', '1', '', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(114, 'nonacp', 'log', 'yes_no', '', '1', '', '1', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(115, 'inemail', 'log', 'yes_no', '', '1', '', '1', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(34, 'enable', 'news', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(35, 'dashboard', 'news', 'yes_no', '', '1', '', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(36, 'page', 'news', 'yes_no', '', '1', '', '1', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(37, 'page_amount', 'news', 'textfield', '', '10', '', '10', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(38, 'comments', 'news', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['news_comments'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['news_comments'] ) ."', '1', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(39, 'rte', 'news', 'yes_no', '', '1', '', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(41, 'guest', 'eunotify', 'yes_no', '', '1', '', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(42, 'guest_upgrade', 'user', 'yes_no', '', '1', '', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(43, 'cookie_domain', 'general', 'textfield', '', '', '', '', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(44, 'cookie_prefix', 'general', 'textfield', '', '', '', '', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(45, 'cookie_path', 'general', 'textfield', '', '', '', '', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(46, 'suggest', 'ticket', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['ticket_suggest'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['ticket_suggest'] ) ."', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(104, 'sidebar_recent', 'kb', 'yes_no', '', '1', '', '1', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(48, 'time_offset', 'general', 'textfield', '', '', '', '', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(50, 'password_expire', 'security', 'textfield', '', '1', '', '1', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(51, 'email_expire', 'security', 'textfield', '', '168', '', '168', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(53, 'transport', 'email', 'dropdown', 'a:3:{s:4:\"smtp\";s:27:\"{lang.email_transport_smtp}\";s:8:\"sendmail\";s:31:\"{lang.email_transport_sendmail}\";s:4:\"mail\";s:27:\"{lang.email_transport_mail}\";}', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['transport'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['transport'] ) ."', 'smtp', 1, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(54, 'smtp_host', 'email', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_host'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_host'] ) ."', '', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(55, 'smtp_port', 'email', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_port'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_port'] ) ."', '25', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(56, 'smtp_user', 'email', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_user'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_user'] ) ."', '', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(57, 'smtp_pass', 'email', 'password', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_pass'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_pass'] ) ."', '', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(58, 'smtp_encryption', 'email', 'dropdown', 'a:3:{i:0;s:33:\"{lang.email_smtp_encryption_none}\";s:3:\"ssl\";s:32:\"{lang.email_smtp_encryption_ssl}\";s:3:\"tls\";s:32:\"{lang.email_smtp_encryption_tls}\";}', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_encryption'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_encryption'] ) ."', '0', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(59, 'sendmail_command', 'email', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['sendmail_command'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['sendmail_command'] ) ."', '', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(60, 'html', 'email', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['html'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['html'] ) ."', '1', 0, 11, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(63, 'tformat_time', 'general', 'textfield', '', 'g:i A', '', 'g:i A', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(61, 'tformat_long', 'general', 'textfield', '', 'M j Y, g:i A', '', 'M j Y, g:i A', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(64, 'mask', 'ticket', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['ticket_mask'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['other']['ticket_mask'] ) ."', '%A%A%A-%n%n%n%n', 1, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(65, 'escalate', 'ticket', 'yes_no', '', '1', '', '1', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(123, 'track', 'ticket', 'yes_no', '', '1', '', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(68, 'vcheck_share', 'security', 'radio', 'a:3:{i:2;s:3:\"Yes\";i:1;s:16:\"Yes, Anonymously\";i:0;s:2:\"No\";}', '2', '0', '2', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(69, 'tformat_date', 'general', 'textfield', '', 'n/j/y', '', 'n/j/y', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(70, 'smtp_timeout', 'email', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_timeout'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['smtp_timeout'] ) ."', '10', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(79, 'enable', 'eunotify', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(74, 'escalate', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(75, 'hold', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(76, 'move', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(77, 'close', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(78, 'reopen', 'eunotify', 'enabled_disabled', '', '1', '', '1', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(92, 'method', 'antispam', 'dropdown', 'a:3:{s:7:\"akismet\";s:34:\"{lang.set_antispam_method_akismet}\";s:10:\"phpcaptcha\";s:37:\"{lang.set_antispam_method_phpcaptcha}\";s:9:\"recaptcha\";s:36:\"{lang.set_antispam_method_recaptcha}\";}', 'recaptcha', 'akismet', 'akismet', 1, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(80, 'enable', 'email', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['enable'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['email']['enable'] ) ."', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(81, 'reply', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(82, 'ticket', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(83, 'enable', 'esnotify', 'yes_no', '', '1', '', '1', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(84, 'assign', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(85, 'escalate', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(86, 'hold', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(87, 'move_to', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(88, 'move_away', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(89, 'close', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(90, 'reopen', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 11, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(93, 'akismet_key', 'antispam', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['akismet_key'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['akismet_key'] ) ."', '', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(94, 'recaptcha_key_public', 'antispam', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['recaptcha_key_public'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['recaptcha_key_public'] ) ."', '', 0, 4, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(95, 'recaptcha_key_private', 'antispam', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['recaptcha_key_private'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['recaptcha_key_private'] ) ."', '', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(96, 'protect_registration', 'antispam', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['protect_registration'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['protect_registration'] ) ."', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(97, 'protect_tickets', 'antispam', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['protect_tickets'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['protect_tickets'] ) ."', '1', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(98, 'protect_forgot_pass', 'antispam', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['protect_forgot_pass'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['protect_forgot_pass'] ) ."', '1', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(101, 'port', 'antispam', 'textfield', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['port'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['port'] ) ."', '80', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(102, 'ssl', 'antispam', 'yes_no', '', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['ssl'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['antispam']['ssl'] ) ."', '0', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(103, 'user_approve', 'esnotify', 'enabled_disabled', '', '1', '', '1', 0, 2, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(105, 'sidebar_views', 'kb', 'yes_no', '', '1', '', '1', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(106, 'sidebar_rating', 'kb', 'yes_no', '', '1', '', '1', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(108, 'rating_threshold', 'kb', 'textfield', '', '3', '', '3', 0, 3, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(109, 'sidebar_rating_count', 'kb', 'textfield', '', '5', '5', '5', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(110, 'force_login', 'security', 'yes_no', '', '0', '', '0', 0, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(116, 'outemail', 'log', 'yes_no', '', '1', '', '1', 0, 5, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(117, 'kb', 'log', 'yes_no', '', '1', '', '1', 0, 6, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(118, 'news', 'log', 'yes_no', '', '1', '', '1', 0, 7, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(119, 'security', 'log', 'yes_no', '', '1', '', '1', 0, 8, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(120, 'ticket', 'log', 'yes_no', '', '1', '', '1', 0, 9, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(121, 'user', 'log', 'yes_no', '', '1', '0', '1', 0, 10, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings` VALUES(122, 'other', 'log', 'yes_no', '', '1', '1', '1', 0, 11, 1);";

$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(1, 'general', 14, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(2, 'security', 10, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(3, 'ticket', 8, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(4, 'kb', 4, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(5, 'ban', 3, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(6, 'look', 2, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(7, 'news', 8, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(8, 'email', 7, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(9, 'eunotify', 10, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(10, 'esnotify', 10, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(11, 'antispam', 7, 0);";
$SQL[] = "INSERT INTO `". $db_prefix ."settings_groups` VALUES(12, 'log', 9, 0);";

$SQL[] = "INSERT INTO `". $db_prefix ."skins` VALUES(1, 'Trellis Desk Default', 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."skins` VALUES(2, 'Trellis Desk Classic', 0, 0);";

$SQL[] = "INSERT INTO `". $db_prefix ."statuses` VALUES(1, 'New', 'Open', '', '', 1, 1, 1);";
$SQL[] = "INSERT INTO `". $db_prefix ."statuses` VALUES(2, 'Open', 'Open', '', '', 2, 1, 2);";
$SQL[] = "INSERT INTO `". $db_prefix ."statuses` VALUES(3, 'In Progress', 'In Progress', '', '', 3, 1, 3);";
$SQL[] = "INSERT INTO `". $db_prefix ."statuses` VALUES(4, 'On Hold', 'On Hold', '', '', 4, 1, 4);";
$SQL[] = "INSERT INTO `". $db_prefix ."statuses` VALUES(5, 'Awaiting User Action', 'Awaiting User Action', 'AUA', 'AUA', 5, 1, 5);";
$SQL[] = "INSERT INTO `". $db_prefix ."statuses` VALUES(6, 'Closed', 'Closed', '', '', 6, 1, 6);";

$SQL[] = "INSERT INTO `". $db_prefix ."upg_history` VALUES(1, ". mysql_real_escape_string( intval( $this->trellis->version_number ) ) .", '". mysql_real_escape_string( $this->trellis->version_name ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['admin']['name'] ) ."', '', ". mysql_real_escape_string( intval( time() ) ) .");";

$SQL[] = "INSERT INTO `". $db_prefix ."users` VALUES(1, '". mysql_real_escape_string( $this->trellis->cache->data['install']['admin']['name'] ) ."', '". mysql_real_escape_string( $this->trellis->cache->data['install']['admin']['email'] ) ."', '". mysql_real_escape_string( $pwhash ) ."', '". mysql_real_escape_string( $pwsalt ) ."', '', 4, '', 0, 'Administrator', '', 1, 0, '". mysql_real_escape_string( $this->trellis->cache->data['install']['admin']['time_zone'] ) ."', ". mysql_real_escape_string( intval( $this->trellis->cache->data['install']['admin']['time_dst'] ) ) .", ". mysql_real_escape_string( intval( $this->trellis->cache->data['install']['admin']['rte_enable'] ) ) .", 1, 1, 1, 1, 1, '". mysql_real_escape_string( $rkhash ) ."', 1, 1, 0, 0, 1, 1, ". mysql_real_escape_string( intval( time() ) ) .", '". mysql_real_escape_string( $this->trellis->input['ip_address'] ) ."');";

$SQL[] = "INSERT INTO `". $db_prefix ."users_staff` VALUES(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'a:7:{s:2:\"id\";s:2:\"4%\";s:4:\"mask\";s:2:\"6%\";s:7:\"subject\";s:3:\"30%\";s:8:\"priority\";s:3:\"13%\";s:10:\"department\";s:3:\"18%\";s:5:\"reply\";s:3:\"16%\";s:6:\"status\";s:3:\"13%\";}', 'reply', 0, 'a:4:{i:0;s:1:\"1\";i:1;s:1:\"2\";i:2;s:1:\"3\";i:3;s:1:\"4\";}', 'N;', 'N;', 'N;', 0, 1);";

?>