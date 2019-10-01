<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';

$c=getvalescaped('c', '',true);
if(preg_match('/^[0-9]+/',$c))
    {
    $d=sql_value(sprintf("SELECT last_update as value FROM `librarylink_collection` WHERE collection_ref=%s",$c),'0000-00-00 00:00:00');
    print $d;
    } else print 0;