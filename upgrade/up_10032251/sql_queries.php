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
|    | Trellis Desk Upgrade 10032251 SQL Queries
#======================================================
*/

$SQL[] = "INSERT INTO `". DB_PRE ."upg_history` VALUES (NULL, '". $this->u_ver_id ."', '". $this->u_ver_human ."', '". time() ."', '". $this->ifthd->member['name'] ."', '". $this->ukey ."');";

?>