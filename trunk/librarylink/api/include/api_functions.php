<?php
/*
 * API v1 : 
 *
 * Library Link API functions
 */

 function lldebug($message)
    {
        global $librarylink_debug_enable,$librarylink_debug_file;
        if($librarylink_debug_enable)
            {
            if($fp=fopen($librarylink_debug_file,'a'))
                {
                if(!is_array($message) and !is_object($message)) fwrite($fp,$message."\n");
                else fwrite($fp,print_r($message,1));
                fclose($fp);
                }
            }
    }


function librarylink_add_resource_link($ref, $xg_type, $xg_key, $xg_rank, $add_keywords)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
        
        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

        //lldebug($resource);
        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        $id = sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
        if($id>0) { return (object)array('error'=>'a record link with that xgtype and xgkey already exists for this resource'); }
        
        sql_query(sprintf("INSERT into librarylink_link (ref,xgtype,xgkey,xgrank,label) values (%s,'%s','%s',%s,'%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,''));
        $id=sql_insert_id();
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,'Add Link',escape_check($user),date('Y-m-d H:i:s')));

        return $id; //link id
    }

function librarylink_delete_resource_link($ref, $xg_type, $xg_key, $delete_keywords)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        
        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
        if($id===0) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }
        
        sql_query(sprintf("DELETE from librarylink_link where id=%s",$id));
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),0,'Delete Link',escape_check($user),date('Y-m-d H:i:s')));

        return $id; //link id
    }

function librarylink_modify_resource_link($ref, $xg_type, $xg_key, $xg_rank)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
        
        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }
        
        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
        if($id===0) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }

        sql_query(sprintf("UPDATE librarylink_link set xgrank=%s where id=%s",$xg_rank,$id));
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,'Update Link Rank',escape_check($user),date('Y-m-d H:i:s')));

        return $id; //link id
    }

function librarylink_delete_links($xg_type, $xg_key, $delete_keywords)
    {
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }

        $resource_ids = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key)));
        if(count($resource_ids)===0) { return (object)array('error'=>'no resources were found with that xgtype and xgkey'); }
        for($i=0;$i<count($resource_ids);$i++) $resource_ids[$i]=(int)$resource_ids[$i]; //cast all result to int

        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        sql_query(sprintf("DELETE from librarylink_link where ref in (%s) and xgtype='%s' and xgkey='%s'",implode(',',$resource_ids),escape_check($xg_type),escape_check($xg_key)));
        foreach($resource_ids as $ref)
            {
                sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),0,'Delete Link',escape_check($user),date('Y-m-d H:i:s')));
            }
        
        return $resource_ids; //list of resource ids
    }