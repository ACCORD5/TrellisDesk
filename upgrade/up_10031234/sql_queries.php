<?php

/*
#======================================================
|    Trellis Desk
|    =====================================
|    By DJ Tarazona (dj@accord5.com)
|    (c) 2008 ACCORD5
|    http://www.trellisdesk.com/
|    =====================================
|    Email: sales@accord5.com
#======================================================
|    @ Version: v1.0 RC 2 Build 10032251
|    @ Version Int: 100.3.2.251
|    @ Version Num: 10032251
|    @ Build: 0251
#======================================================
|    | Trellis Desk Upgrade 10031234 SQL Queries
#======================================================
*/

$SQL[] = "INSERT INTO `". DB_PRE ."settings` VALUES (NULL, 'val_hours_p', 'Password Validation Expiration', 'The amount of hours in which a reset password validation code will expire.', 2, 'input', '1', '', '1', 1, 5, 1);";
$SQL[] = "INSERT INTO `". DB_PRE ."settings` VALUES (NULL, 'val_hours_e', 'Email Validation Expiration', 'The amount of hours in which a email validation code will expire.', 2, 'input', '168', '', '168', 1, 4, 1);";
$SQL[] = "INSERT INTO `". DB_PRE ."settings` VALUES (NULL , 'acp_help', 'Show ACP Inline Help', 'If set to yes, additional documentation will be available for several ACP settings. To view this information, simply click the Toggle Information link.', 1, 'yes_no', '1', '', '1', 1, 15, 1);";

$SQL[] = "INSERT INTO `". DB_PRE ."upg_history` VALUES (NULL, '". $this->u_ver_id ."', '". $this->u_ver_human ."', '". time() ."', '". $this->ifthd->member['name'] ."', '". $this->ukey ."');";

?>