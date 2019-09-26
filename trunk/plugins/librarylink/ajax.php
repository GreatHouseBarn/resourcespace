<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';

$c=getvalescaped('c', '',true);
if(preg_match('/^[0-9]+/',$c))
    {
    $d=sql_value(sprintf("SELECT max(date) as value FROM `collection_log` WHERE user=%s and collection=%s",$userref,$c),'0000-00-00 00:00:00');
    print $d;
    } else print '0000-00-00 00:00:00';