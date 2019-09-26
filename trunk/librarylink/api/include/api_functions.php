<?php
/*
 * API v1 : 
 *
 * Library Link API functions
 */

function lldebug($message, $title='')
    {
        global $librarylink_debug_enable,$librarylink_debug_file;
        if($librarylink_debug_enable)
            {
            if($fp=fopen($librarylink_debug_file,'a'))
                {
                if($title!='') fwrite($fp,"$title :");
                if(!is_array($message) and !is_object($message)) fwrite($fp,$message."\n");
                else fwrite($fp,print_r($message,1));
                fclose($fp);
                }
            }
    }

function librarylink_execute_api_call($query,$pretty=true)
    {
    // Execute the specified API function.
    $params=array();parse_str($query,$params);
    if (!array_key_exists("function",$params)) {return false;}
    $function=$params["function"];
    if (!function_exists("api_" . $function)) {return false;}
    
    // $content_type='json';
    // if(isset($params["content-type"]))
    //     {
    //     $content_type=$params["content-type"];
    //     unset($params["content-type"]);
    //     }
    // if(!in_array($content_type,array('json','raw'))) $content_type='json';
    
    // Construct an array of the real params, setting default values as necessary
    $setparams = array();
    $n = 0;    
    $fct = new ReflectionFunction("api_" . $function);
    foreach ($fct->getParameters() as $fparam)
        {
        $paramkey = $n + 1;
        debug ("API Checking for parameter " . $fparam->getName() . " (param" . $paramkey . ")");
        if (array_key_exists("param" . $paramkey,$params) && $params["param" . $paramkey] != "")
            {
            debug ("API " . $fparam->getName() . " -   value has been passed : '" . $params["param" . $paramkey] . "'");
            $setparams[$n] = $params["param" . $paramkey];
            }
        
        elseif ($fparam->isOptional())
            {
            // Set default value if nothing passed e.g. from API test tool
            debug ("API " . $fparam->getName() . " -  setting default value = '" . $fparam->getDefaultValue() . "'");
            $setparams[$n] = $fparam->getDefaultValue();
            }
        else
            {
             // Set as empty
            debug ("API " . $fparam->getName() . " -  setting null value = '" . $fparam->getDefaultValue() . "'");
            $setparams[$n] = "";    
            }
        $n++;
        }
    
    debug("API - calling api_" . $function);
    $result = call_user_func_array("api_" . $function, $setparams);
    if(is_array($result) or is_object($result) or is_bool($result)) $content_type='json'; else $content_type='raw';
    
    if($content_type=='json')
        {
        if($pretty)
            {
                debug("API: json_encode() using JSON_PRETTY_PRINT");
                return json_encode($result,(defined('JSON_PRETTY_PRINT')?JSON_PRETTY_PRINT:0));
            }
        else
            {
                debug("API: json_encode()");
                $json_encoded_result = json_encode($result);

                if(json_last_error() !== JSON_ERROR_NONE)
                    {
                    debug("API: JSON error: " . json_last_error_msg());
                    debug("API: JSON error when \$result = " . print_r($result, true));
                    }

                return $json_encoded_result;
            }
        }

    if($content_type=='raw') return $result;

    }


// function librarylink_add_resource_link($ref, $xg_type, $xg_key, $xg_rank, $add_keywords)
//     {
//         if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
//         if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
//         if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
        
//         $resource=get_resource_data($ref, true);
//         if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

//         //lldebug($resource);
//         global $userref;
//         $userinfo=get_user($userref);
//         $user=$userinfo["username"] . "-" . $userinfo["fullname"];

//         $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
//         if($id===0)
//             {
//             db_begin_transaction();
//             librarylink_update_ranks($xg_type, $xg_key, $xg_rank); //shift ranks up by one where we want to place a rank
//             sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,"Move Links Ranks >= $xg_rank up one",escape_check($user),date('Y-m-d H:i:s')));
//             sql_query(sprintf("INSERT into librarylink_link (ref,xgtype,xgkey,xgrank,label) values (%s,'%s','%s',%s,'%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,''));
//             $id=sql_insert_id();
//             sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,'Add Link',escape_check($user),date('Y-m-d H:i:s')));
//             db_end_transaction();
//             } else {
//                 return (object)array('error'=>'a record link with that xgtype and xgkey already exists for this resource');
//             }

//         return $id; //link id
//     }

//add a resource by id to a librarylink_collection with xg_type and xg_key
function librarylink_add_resource_link($ref, $xg_type, $xg_key, $label='', $xg_rank=1, $add_keywords=true)
    {
    if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
    if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
    if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
    
    $resource=get_resource_data($ref, true);
    if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

    //find or create the collection for this xgtype and xgkey pair
    if($collection=librarylink_create_linked_collection($xg_type,$xg_key,$label))
        {
        if(in_array($ref,$collection['resources'])) return (object)array('error'=>'a record link with that xgtype and xgkey already exists for this resource');
        db_begin_transaction();
        if($collection['resource_count']>0) librarylink_update_ranks($xg_type, $xg_key, $xg_rank); //shift ranks up by one where we want to place a rank
        if(add_resource_to_collection($ref,$collection['ref']))
            {   //at this point the new resource's sortorder will be null so update it
            librarylink_set_rank_for_resource_by_collection_id($ref,$collection['ref'],$xg_rank);
            if($add_keywords) librarylink_add_keyword_to_resource($ref,$collection['ref']);
            }
        db_end_transaction();
        librarylink_operation_log($ref,$xg_type, $xg_key, $xg_rank,'Add resource to record');
        }
    $collection=librarylink_get_linked_collection_by_id($collection['ref']);
    return $collection;
    }
// function librarylink_delete_resource_link($ref, $xg_type, $xg_key, $delete_keywords)
//     {
//         if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
//         if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        
//         $resource=get_resource_data($ref, true);
//         if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

//         global $userref;
//         $userinfo=get_user($userref);
//         $user=$userinfo["username"] . "-" . $userinfo["fullname"];

//         $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
//         if($id===0) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }
        
//         sql_query(sprintf("DELETE from librarylink_link where id=%s",$id));
//         sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),0,'Delete Link',escape_check($user),date('Y-m-d H:i:s')));

//         return $id; //link id
//     }

function librarylink_delete_resource_link($ref, $xg_type, $xg_key, $delete_keywords=true)
    {
    if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
    if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
    
    $resource=get_resource_data($ref, true);
    if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

    //find or create the collection for this xgtype and xgkey pair
    if($collection=librarylink_get_linked_collection($xg_type, $xg_key))
        {
        if(!in_array($ref,$collection['resources'])) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }
        db_begin_transaction();
        if(remove_resource_from_collection($ref,$collection['ref']))
            {   
            if($delete_keywords) { librarylink_remove_keyword_from_resource($ref,$collection['ref']); }
            }
        //at this point the removed resource will have left a hole in the ranks
        if($collection['resource_count']>0) { librarylink_set_ranks_by_collection_id($collection['ref']); } //reorder the ranks
        db_end_transaction();
        librarylink_operation_log($ref,$xg_type, $xg_key, 0,'Remove resource from record');
        }
    $collection=librarylink_get_linked_collection_by_id($collection['ref']);
    return $collection;
    }



// function librarylink_modify_resource_link($ref, $xg_type, $xg_key, $xg_rank)
//     {
//         if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
//         if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
//         if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
        
//         $resource=get_resource_data($ref, true);
//         if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }
        
//         global $userref;
//         $userinfo=get_user($userref);
//         $user=$userinfo["username"] . "-" . $userinfo["fullname"];

//         $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
//         if($id===0) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }

//         db_begin_transaction();
//         librarylink_update_ranks($xg_type, $xg_key, $xg_rank); //shift ranks up by one where we want to place a rank
//         sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,"Move Links Ranks >= $xg_rank up one",escape_check($user),date('Y-m-d H:i:s')));
//         sql_query(sprintf("UPDATE librarylink_link set xgrank=%s where id=%s",$xg_rank,$id));
//         sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,'Update Link Rank',escape_check($user),date('Y-m-d H:i:s')));
//         db_end_transaction();

//         return $id; //link id
//     }

function librarylink_modify_resource_link_rank($ref, $xg_type, $xg_key, $xg_rank)
    {
    if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
    if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
    if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
    
    $resource=get_resource_data($ref, true);
    if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }
        
    //find or create the collection for this xgtype and xgkey pair
    if($collection=librarylink_get_linked_collection($xg_type, $xg_key))
        {
        if(!in_array($ref,$collection['resources'])) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }
        db_begin_transaction();
        
        if($collection['resource_count']>1)           
            {
            librarylink_set_rank_for_resource_by_collection_id($ref,$collection['ref'],999999); //move our resource out of the way
            librarylink_set_ranks_by_collection_id($collection['ref']); //reorder the ranks
            librarylink_update_ranks($xg_type, $xg_key, $xg_rank); //shift ranks up by one where we want to place a rank
            } 
        librarylink_set_rank_for_resource_by_collection_id($ref,$collection['ref'],$xg_rank); //move our rank into that space
        if($collection['resource_count']>1) { librarylink_set_ranks_by_collection_id($collection['ref']); } //reorder the ranks
        db_end_transaction();
        librarylink_operation_log($ref,$xg_type, $xg_key, $xg_rank,'Change resource rank');
        }
    $collection=librarylink_get_linked_collection_by_id($collection['ref']);
    return $collection;
    }


// function librarylink_delete_links($xg_type, $xg_key, $delete_keywords)
//     {
//         if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }

//         $resource_ids = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key)));
//         if(count($resource_ids)===0) { return (object)array('error'=>'no resources were found with that xgtype and xgkey'); }
//         for($i=0;$i<count($resource_ids);$i++) $resource_ids[$i]=(int)$resource_ids[$i]; //cast all result to int

//         global $userref;
//         $userinfo=get_user($userref);
//         $user=$userinfo["username"] . "-" . $userinfo["fullname"];

//         sql_query(sprintf("DELETE from librarylink_link where ref in (%s) and xgtype='%s' and xgkey='%s'",implode(',',$resource_ids),escape_check($xg_type),escape_check($xg_key)));
//         foreach($resource_ids as $ref)
//             {
//                 sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),0,'Delete Link',escape_check($user),date('Y-m-d H:i:s')));
//             }
        
//         return $resource_ids; //list of resource ids
//     }

//remove all linked resources from a record (and optionally remove the linked collection too)
function librarylink_delete_links($xg_type, $xg_key, $delete_keywords=true, $delete_collection=false)
    {
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }

        if($collection_id=sql_value(sprintf("SELECT collection as value from librarylink_collection where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key)),false))
        //get a list of resources that are in this librarylink collection
        $resource_ids=sql_array(sprintf("SELECT resource as value from librarylink_collection left join collection_resource on collection_ref=collection where collection_ref=%s order by link,sortorder asc,date_added desc",$collection_id));       
        foreach($resource_ids as $ref)
            {
            if($delete_keywords) librarylink_remove_keyword_from_resource($ref,$collection_id);
            if(remove_resource_from_collection($ref,$collection_id))
                {
                lldebug(sprintf("Removed resource: %s from collection: %s",$ref,$collection_id));
                }
            }
            if($delete_collection) 
                {
                delete_collection($collection_id);
                lldebug(sprintf("Deleted collection with id: %s",$collection_id));
                }

        librarylink_operation_log('',$xg_type, $xg_key,0,'Removed all resources from link');
        return $resource_ids;
    }

// function librarylink_delete_links_by_ref($ref, $delete_keywords)
//     {
//         if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }

//         $resource=get_resource_data($ref, true);
//         if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

//         global $userref;
//         $userinfo=get_user($userref);
//         $user=$userinfo["username"] . "-" . $userinfo["fullname"];

//         sql_query(sprintf("DELETE from librarylink_link where ref=%s",$ref));
//         sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,'','',0,'Delete all links',escape_check($user),date('Y-m-d H:i:s')));
        
//         return true;
//     }

//deletes all record links that a resource may have (and optionally check if the resource actually existed)
function librarylink_delete_links_by_ref($ref, $delete_keywords=true, $check_exists=true)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }

        if($check_exists)
            {
            $resource=get_resource_data($ref, true);
            if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }
            }

        //get a list of librarylink collections that contain this resource
        $collections=sql_array(sprintf("SELECT collection as value from librarylink_collection left join collection_resource on collection_ref=collection where resource=%s order by link,sortorder asc,date_added desc",$ref));       
        foreach($collections as $collection)
            {
            if($delete_keywords) librarylink_remove_keyword_from_resource($ref,$collection['collection']);
            if(remove_resource_from_collection($ref,$collection['collection']))
                {
                lldebug(sprintf("Removed resource: %s from collection: %s",$ref,$collection['collection']));
                }
            }

        librarylink_operation_log($ref,'','',0,'Delete all links for resource');        
        return true;
    }

function librarylink_operation_log($resource,$xg_type,$xg_key,$xg_rank,$operation)
    {
    global $userref;
    $userinfo=get_user($userref);
    $user=$userinfo["username"] . "-" . $userinfo["fullname"];
    $time=date('Y-m-d H:i:s');
    sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values ('%s','%s','%s',%s,'%s','%s','%s')",escape_check($resource),escape_check($xg_type),escape_check($xg_key),escape_check($xg_rank),escape_check($operation),escape_check($user),escape_check($time)));
    }

// function librarylink_get_all_links()
//     {
//         $records=sql_query("SELECT concat(xgtype,' - ',xgkey) as link, ref from librarylink_link order by xgtype,xgkey,xgrank");
//         $result=array();
//         if(count($records))
//             {
//             foreach($records as $r) $result[$r['link']][]=$r['ref'];
//             }
//         return $result;
//     }
function librarylink_get_all_links()
    {
    $records=sql_query("SELECT concat(xgtype,'_',xgkey) as link, resource from librarylink_collection left join collection_resource on collection_ref=collection order by link,sortorder asc,date_added desc");
    $result=array();
    if(count($records))
        {
        foreach($records as $r) $result[$r['link']][]=$r['resource'];
        }
    lldebug($result);
    return $result;
    }

// function librarylink_do_search($xg_type, $xg_key, $fetchrows, $sort)
//     {
//         $resources=array();
//         if(!preg_match('/^[0-9]+$/',$fetchrows)) $fetchrows=0;
//         if(!in_array($sort,array('asc','desc'))) $sort='asc';
//         $limit=$fetchrows>0?'limit '.$fetchrows:'';
//         $order=' ORDER BY xgrank '.$sort;

//         if($xg_type=="" and $xg_key=="") { return $resources; }
//         if($xg_type=="") { $links = sql_query(sprintf("SELECT * from librarylink_link where xgkey='%s' %s %s",escape_check($xg_key),$order,$limit)); }
//         elseif($xg_key=="") { $links = sql_query(sprintf("SELECT * from librarylink_link where xgtype='%s' %s %s",escape_check($xg_type),$order,$limit)); }
//         else $links = sql_query(sprintf("SELECT * from librarylink_link where xgtype='%s' and xgkey='%s' %s %s",escape_check($xg_type),escape_check($xg_key),$order,$limit));

//         if(count($links)===0) { return $resources; }
//         //lldebug($links);
//         foreach($links as $link)
//         {
//             $resource=get_resource_data($link["ref"], true);
//             $resource['xg_type']=$link['xgtype'];
//             $resource['xg_key']=$link['xgkey'];
//             $resource['xg_rank']=$link['xgrank'];
//             //lldebug($resource);
//             $resources[]=$resource; 
//         }
//         return $resources;
//     }
function librarylink_do_search($xg_type, $xg_key, $fetchrows, $sort)
    {
        $resources=array();
        if(!preg_match('/^[0-9]+$/',$fetchrows)) $fetchrows=0;
        if(!in_array($sort,array('asc','desc'))) $sort='asc';
        $limit=$fetchrows>0?'limit '.$fetchrows:'';
        if($sort=='asc')  $order=' ORDER BY sortorder asc,date_added desc';
        if($sort=='desc') $order=' ORDER BY sortorder desc,date_added asc';

        if($xg_type=='' and $xg_key=='') { return $resources; }
        if($xg_type=='') { $links = sql_query(sprintf("SELECT * from librarylink_collection left join collection_resource on collection_ref=collection where xgkey='%s' %s %s",escape_check($xg_key),$order,$limit)); }
        elseif($xg_key=='') { $links = sql_query(sprintf("SELECT * from librarylink_collection left join collection_resource on collection_ref=collection where xgtype='%s' %s %s",escape_check($xg_type),$order,$limit)); }
        else $links = sql_query(sprintf("SELECT * from librarylink_collection left join collection_resource on collection_ref=collection where xgtype='%s' and xgkey='%s' %s %s",escape_check($xg_type),escape_check($xg_key),$order,$limit));

        if(count($links)===0) { return $resources; }
        //lldebug($links);
        foreach($links as $link)
        {
            $resource=get_resource_data($link["resource"], true);
            $resource['xg_type']=$link['xgtype'];
            $resource['xg_key']=$link['xgkey'];
            $resource['xg_rank']=$link['sortorder'];
            $resource['label']=$link['label'];
            //lldebug($resource);
            $resources[]=$resource; 
        }
        return $resources;
    }

// function librarylink_get_ranks($xg_type, $xg_key)
//     {
//         $resources=array();
//         if($xg_type=="" or $xg_key=="") { return $resources; }        
//         $links = sql_query(sprintf("SELECT ref,xgrank from librarylink_link where xgtype='%s' and xgkey='%s' order by xgrank asc",escape_check($xg_type),escape_check($xg_key)));
//         if(count($links)===0) { return $resources; }
//         foreach($links as $link)
//             {
//             $resources[$link['ref']]=$link['xgrank'];
//             }
//         return $resources;        
//     }

function librarylink_get_ranks($xg_type, $xg_key)
    {
        $resources=array();
        if($xg_type=="" or $xg_key=="") { return $resources; }        
        $links = sql_query(sprintf("SELECT resource,sortorder from librarylink_collection left join collection_resource on collection_ref=collection where xgtype='%s' and xgkey='%s' ORDER BY sortorder asc,date_added desc",escape_check($xg_type),escape_check($xg_key)));
        if(count($links)===0) { return $resources; }
        foreach($links as $link)
            {
            $resources[$link['resource']]=$link['sortorder'];
            }
        return $resources;        
    }

//make sure that all resources in a collection have a sort order (and not null)
function librarylink_set_ranks($xg_type, $xg_key)
    {
        if($collection_id=sql_value(sprintf("SELECT collection as value from librarylink_collection where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key)),false))
            {
            librarylink_set_ranks_by_collection_id($collection_id);
            }
    }
//make sure that all resources in a collection (by id) have a sort order (and not null)
function librarylink_set_ranks_by_collection_id($collection_id)
    {
    if(preg_match('/^[0-9]+$/',$collection_id) and $collection_id>0)
        {
        $ranks=sql_array(sprintf("SELECT resource as value from collection_resource where collection=%s ORDER BY sortorder asc,date_added desc",$collection_id));
        update_collection_order($ranks,$collection_id);
        lldebug(sprintf("Resource order ranks set for resources: %s",implode(',',$ranks)));
        }        
    }

function librarylink_set_rank_for_resource_by_collection_id($ref,$collection_id, $xg_rank)
    {
    if(!(preg_match('/^[0-9]+$/',$ref) and $ref>0)) return;
    if(!(preg_match('/^[0-9]+$/',$collection_id) and $collection_id>0)) return;
    if(!(preg_match('/^[0-9]+$/',$xg_rank) and $xg_rank>0)) return;
    
    $sql=sprintf("update collection_resource set sortorder=%s where collection=%s and resource=%s",$xg_rank,$collection_id,$ref);
    sql_query($sql);
    lldebug(sprintf("Set resource: %s in collection: %s to rank: %s",$ref,$collection_id,$xg_rank));
    }

// function librarylink_update_ranks($xg_type, $xg_key, $xg_rank)
//     {
//         if(!preg_match('/^[0-9]+$/',$xg_rank)) $xg_rank=1;
//         sql_query(sprintf("UPDATE librarylink_link set xgrank=xgrank+1 where xgtype='%s' and xgkey='%s' and xgrank>=%s",escape_check($xg_type),escape_check($xg_key),$xg_rank));
//     }

//shift ranks up on above and equal to the rank that we specify to make space for a new resource to take that rank
function librarylink_update_ranks($xg_type, $xg_key, $xg_rank)
    {
        if(!preg_match('/^[0-9]+$/',$xg_rank)) $xg_rank=1;
        sql_query(sprintf("UPDATE librarylink_collection left join collection_resource on collection_ref=collection set sortorder=sortorder+1 where xgtype='%s' and xgkey='%s' and sortorder>=%s",escape_check($xg_type),escape_check($xg_key),$xg_rank));
    }

//add a keyword to a resource based upon the xgtype and xgkey of the librarylink collection which is specified by id
function librarylink_add_keyword_to_resource($ref,$usercollection)
    {
    global $librarylink_record_keywords_field;
    if($collection=librarylink_get_linked_collection_by_id($usercollection))
        {
        $keyword=sprintf("%s_%s",$collection['xgtype'],$collection['xgkey']);
        add_keyword_mappings($ref,$keyword,$librarylink_record_keywords_field);
        lldebug(sprintf("Added keyword: %s to resource: %s",$keyword,$ref));
        $sql=sprintf("select value from resource_data where resource=%s and resource_type_field=%s",$ref,$librarylink_record_keywords_field);
        $existing = sql_value($sql,false);
        if(false!==$existing)
            {
            $values=explode(' ',trim($existing));
            $values[]=$keyword;
            $values=array_unique($values);
            $new=implode(' ',$values);
            $sql=sprintf("update resource_data set value='%s' where resource=%s and resource_type_field=%s",escape_check($new),$ref,$librarylink_record_keywords_field);
            sql_query($sql);
            lldebug(sprintf("Updated field: %s in resource: %s to: %s",$librarylink_record_keywords_field,$ref,$new));
            } else {
            $sql=sprintf("insert into resource_data(resource,resource_type_field,value) values (%s,%s,'%s')",$ref,$librarylink_record_keywords_field,escape_check($keyword));
            sql_query($sql);
            lldebug(sprintf("Inserted field: %s in resource: %s to: %s",$librarylink_record_keywords_field,$ref,$keyword));
            }
        }               
    }

//remove a keyword from a resource based upon the xgtype and xgkey of the librarylink collection which is specified by id
function librarylink_remove_keyword_from_resource($ref,$usercollection)
    {
    global $librarylink_record_keywords_field;
    if($collection=librarylink_get_linked_collection_by_id($usercollection))
        {
        $keyword=sprintf("%s_%s",$collection['xgtype'],$collection['xgkey']);
        remove_keyword_mappings($ref,$keyword,$librarylink_record_keywords_field);
        lldebug(sprintf("Removed keyword: %s from resource: %s",$keyword,$ref));
        $sql=sprintf("select value from resource_data where resource=%s and resource_type_field=%s",$ref,$librarylink_record_keywords_field);
        $existing = sql_value($sql,false);
        if(false!==$existing)
            {
            $values=explode(' ',trim($existing));
            $values=array_diff($values,array($keyword));
            $values=array_unique($values);
            $new=implode(' ',$values);
            $sql=sprintf("update resource_data set value='%s' where resource=%s and resource_type_field=%s",escape_check($new),$ref,$librarylink_record_keywords_field);
            sql_query($sql);
            lldebug(sprintf("Updated field: %s in resource: %s to: %s",$librarylink_record_keywords_field,$ref,$new));
            }
        }              
    }

/* These functions manage the specific librarylink collections for each record (xgtype/xgkey)

*/
function librarylink_get_linked_collection($xg_type, $xg_key)
    {
    $xg_type=trim($xg_type);
    $xg_key=trim($xg_key);
    if($xg_type=='' or $xg_key=='') return false;
    $sql=sprintf("select collection_ref,xgtype,xgkey,label,ref,name,description,count(resource) as resource_count from librarylink_collection left join collection on collection_ref=ref left join collection_resource on ref=collection where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key));
    $result=sql_query($sql);
    if(isset($result[0]['collection_ref']) and $result[0]['ref']>0)
        {
            $resources=sql_array(sprintf("SELECT resource as value from librarylink_collection left join collection_resource on collection_ref=collection where xgtype='%s' and xgkey='%s' ORDER BY sortorder asc,date_added desc",escape_check($xg_type),escape_check($xg_key)));
            $result[0]['resources']=$resources;
            return $result[0];
        }
    return false;
    }

function librarylink_get_linked_collection_by_id($ref)
    {
    if(!preg_match('/^[0-9]+$/',$ref)) return false;
    $sql=sprintf("select collection_ref,xgtype,xgkey,label,ref,name,description,count(resource) as resource_count from librarylink_collection left join collection on collection_ref=ref left join collection_resource on ref=collection where collection_ref=%s",$ref);
    $result=sql_query($sql);
    if(isset($result[0]['collection_ref']) and $result[0]['ref']>0)
        {
            $resources=sql_array(sprintf("SELECT resource as value from librarylink_collection left join collection_resource on collection_ref=collection where collection_ref=%s ORDER BY sortorder asc,date_added desc",$ref));
            $result[0]['resources']=$resources;
            return $result[0];
        }
    return false;
    }

function librarylink_create_linked_collection($xg_type, $xg_key, $label='')
    {
    global $lang,$librarylink_api_user_id,$librarylink_collection_name_template;
    $xg_type=trim($xg_type);
    $xg_key=trim($xg_key);
    if($xg_type=='' or $xg_key=='') return false;
    $description=sprintf($lang['librarylink_collection_description'],$xg_type,$label,$xg_key);
    $name=sprintf($lang['librarylink_collection_name'],$xg_type,$label?$label:$xg_key);
    $session=isset($_COOKIE['user'])?$_COOKIE['user']:'';
    
    //check if collection exists and looks ok
    $create_collection=$update_collection=false;
    $collection=librarylink_get_linked_collection($xg_type, $xg_key);    
    if(false===$collection) $create_collection=true;
    if(isset($collection['ref']) and ! ($collection['ref']>0)) $create_collection=true;

    if($label=='') //don't update the name or description if label was empty
        {
        $name=$collection['name'];
        $description=$collection['description'];
        } else {
        if($collection['description']!=$description) $update_collection=true;
        if($collection['name']!=$name) $update_collection=true;
        $collection['label']=$label;
        }    

    if($create_collection)
        { 
        $collection['ref']=create_collection($librarylink_api_user_id, $name, 1, 1, 0, false);
        if($collection['ref']>0) 
            {
            $sql=sprintf("delete from librarylink_collection where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key));
            sql_query($sql);
            $sql=sprintf("insert into librarylink_collection(collection_ref,xgtype,xgkey,label) values (%s,'%s','%s','%s')",$collection['ref'],escape_check($xg_type),escape_check($xg_key),escape_check($label));
            sql_query($sql);        
            lldebug(sprintf("Created collection with id: %s for session: %s and request: %s",$collection['ref'],$session,$_SERVER['REQUEST_URI']));
            $collection=librarylink_get_linked_collection($xg_type, $xg_key); 
            } else {
            $message=sprintf($lang['librarylink_collection_failed'],$name);
            lldebug($message);
            }
        }

    if($update_collection)
        {
        $collection['description']=$description;
        $collection['name']=$name;
        $sql=sprintf("update librarylink_collection left join collection on collection_ref=ref set name='%s',description='%s',label='%s' where collection_ref=%s",escape_check($collection['name']),escape_check($collection['description']),escape_check($collection['label']),$collection['ref']);
        sql_query($sql);        
        lldebug(sprintf("Updated collection with id: %s for session: %s and request: %s",$collection['ref'],$session,$_SERVER['REQUEST_URI']));  
        }

    return $collection;
    }

function librarylink_add_user_to_linked_collection($collection_id)
    {
    global $userref;
    $session=isset($_COOKIE['user'])?$_COOKIE['user']:'';
    if($collection_id>0) 
        {
        //we can't use this - it fills up the logs!!
        //add_collection($userref,$collection_id); //add current user to the collection
        sql_query(sprintf("insert into user_collection (user,collection,request_feedback) values (%s,%s,0)",$userref,$collection_id));
        lldebug(sprintf("Added user: %s to collection: %s for session: %s and request: %s",$userref,$collection_id,$session,$_SERVER['REQUEST_URI']));
        }
    }

function librarylink_remove_user_from_linked_collection($collection_id)
    {
    global $userref;
    $session=isset($_COOKIE['user'])?$_COOKIE['user']:'';
    if($collection_id>0) 
        {
        //we can't use this - it fills up the logs!!
        //remove_collection($userref,$collection_id); //remove current user from the collection
        sql_query(sprintf("delete from user_collection where user=%s and collection=%s",$userref,$collection_id));
        lldebug(sprintf("Removed user: %s from collection: %s for session: %s and request: %s",$userref,$collection_id,$session,$_SERVER['REQUEST_URI']));
        }
    }

function librarylink_remove_linked_collections_from_user($userref,$collection_ids)
    {
        if(!is_array($collection_ids) or count($collection_ids)==0) return;
        $session=isset($_COOKIE['user'])?$_COOKIE['user']:'';
        $collections=implode(',',$collection_ids);        
        $sql=sprintf("delete from user_collection where user=%s and collection in (%s)",$userref,$collections);
        sql_query($sql);
        lldebug(sprintf("Removed collections: %s from user: %s with session: %s and request: %s",$collections,$userref,$session,$_SERVER['REQUEST_URI']));
    }

function librarylink_is_linked_collection($usercollection)
    {
    global $userref;
    $sql=sprintf("select collection_ref as value from librarylink_collection left join user_collection on collection_ref=collection where user=%s",$userref);
    $collection_ids=sql_array($sql);
    if(in_array($usercollection,$collection_ids)) return true;
    return false;
    }

function librarylink_get_linked_collections($userref)
    {
        $sql=sprintf("select collection_ref as value from librarylink_collection left join user_collection on collection_ref=collection where user=%s",$userref);
        $collection_ids=sql_array($sql);
        return $collection_ids;
    }

function librarylink_get_link_parameters()
    {
        global $links_changed;
        $links=array();
        $links_changed=false;
        //get or post values given for ll_type and ll_keys ?
        if(isset($_REQUEST['ll_type'])) $ll_type=trim($_REQUEST['ll_type']); else $ll_type='';
        if(isset($_REQUEST['ll_keys'])) $ll_keys=trim($_REQUEST['ll_keys']); else $ll_keys='';
        if($ll_type!='' and $ll_keys!='') //we have both values
            {
            if(isset($_COOKIE['ll_type']))
                {
                if($ll_type!=$_COOKIE['ll_type']) $links_changed=true; //if get or post != cookie value then links changed
                } else $links_changed=true;                             //if we never had a cookie value then links changed
            if(isset($_COOKIE['ll_keys']))
                {
                if($ll_keys!=$_COOKIE['ll_keys']) $links_changed=true; //if get or post != cookie value then links changed
                } else $links_changed=true;                             //if we never had a cookie value then links changed
            
            if($links_changed) //links changed so set or reset cookies
                {
                rs_setcookie('ll_type',$ll_type,0,"","",false,false);
                rs_setcookie('ll_keys',$ll_keys,0,"","",false,false);
                }
            } else { //no links given in get or post so check cookies
            if(isset($_COOKIE['ll_type'])) $ll_type=$_COOKIE['ll_type']; else $ll_type='';
            if(isset($_COOKIE['ll_keys'])) $ll_keys=$_COOKIE['ll_keys']; else $ll_keys='';  
            }

        if($ll_type=='' or $ll_keys=='') return $links; //nope, no links if neither get,post or cookies

        $keys=explode(',',$ll_keys);
        sort($keys);
        for($i=0;$i<count($keys);$i++)
            {
            $tmp=explode('|',$keys[$i]);
            $links[$i]=array();
            $links[$i]['xg_type']=$ll_type;
            $links[$i]['xg_key']=trim($tmp[0]);
            $links[$i]['label']=isset($tmp[1])?trim($tmp[1]):'';
            }
        lldebug("Links:");
        foreach($links as $link) lldebug(sprintf("%s_%s (%s)",$link['xg_type'],$link['xg_key'],$link['label']));
        return $links;
    }

// end collection functions



function librarylink_create_librarylink_collection()
    {
    global $userref, $collection_allow_creation, $librarylink_collection_name;
    if (checkperm("b") || !$collection_allow_creation)
        {
        return false;
        }
    $collections=get_user_collections($userref);
    $found=false;
    for($i=0;$i<count($collections);$i++)
        {
            if($collections[$i]['name']===$librarylink_collection_name)
                {
                    $found=true;
                    break;
                }
        }
    
    if(!$found) { $collection=create_collection($userref, $librarylink_collection_name, 0); }
    lldebug("Collection $collection created!");
    return $collection;
    }

function librarylink_empty_librarylink_collection($collection)
    {
        if(!preg_match('/^[0-9]+$/',$collection)) return false;
        $collection_description="This is the LibraryLink collection which is currently not holding any linked record resources.\n";
        sql_query(sprintf("update collection set description='%s' where ref=%s",escape_check($collection_description),$collection));
        sql_query("delete from collection_resource where collection='$collection'");
        sql_query("delete from collection_keyword where collection='$collection'");
        lldebug("Collection: $collection emptied!");
        return true;
    }

function is_librarylink_collection($usercollection)
    {
        global $userref, $collection_allow_creation, $librarylink_collection_name;
        if (checkperm("b") || !$collection_allow_creation) { return false; }
        $collections=get_user_collections($userref);
        for($i=0;$i<count($collections);$i++)
            {
                if($collections[$i]['ref']==$usercollection and $collections[$i]['name']===$librarylink_collection_name) return true;
            }
        return false;       
    }

function librarylink_get_all_records()
    {
        $records=sql_array("SELECT distinct concat(xgtype,' / ',xgkey) as value from librarylink_link order by xgtype,xgkey");
        return $records;
    }

function librarylink_get_links_parameters($set_cookies=true)
    {
        $links=array();
        if(isset($_REQUEST['ll_links'])) $ll_links=$_REQUEST['ll_links']; else $ll_links='';
        if($ll_links!='') 
            {
            $links=explode(',',$ll_links);
            for($i=0;$i<count($links);$i++) {
                if(false===strpos($links[$i],'|')) unset($links[$i]); //throw away invalid link pairs
            }
            $ll_links=implode(',',$links);
            if($set_cookies) rs_setcookie('ll_links',$ll_links,0,"","",false,false);
            } else {
            if(isset($_COOKIE['ll_links'])) $ll_links=$_COOKIE['ll_links']; else $ll_links='';
  
            if($ll_links!='') 
                {
                $links=explode(',',$ll_links);
                for($i=0;$i<count($links);$i++) {
                    if(false===strpos($links[$i],'|')) unset($links[$i]); //throw away invalid link pairs
                }
                $ll_links=implode(',',$links);
                if($set_cookies) rs_setcookie('ll_links',$ll_links,0,"","",false,false);
                }
            }
        sort($links);
        for($i=0;$i<count($links);$i++)
            {
            $tmp=explode('|',$links[$i]);
            $links[$i]=array();
            $links[$i]['xg_type']=trim($tmp[0]);
            $links[$i]['xg_key']=trim($tmp[1]);
            }
        return $links;
    }



function librarylink_iframe_header()
    {
        global $baseurl,$css_reload_key;
        return '<!DOCTYPE html>
        <html lang="en">	
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
        <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- Load jQuery-->
        <script src="'.$baseurl.'/lib/js/jquery-3.3.1.min.js"></script>
        <!-- Structure Stylesheet -->
        <link href="'.$baseurl.'/css/global.css?css_reload_key='.$css_reload_key.'" rel="stylesheet" type="text/css" media="screen,projection,print" />
        <!-- Colour stylesheet -->
        <link href="'.$baseurl.'/css/colour.css?css_reload_key='.$css_reload_key.'" rel="stylesheet" type="text/css" media="screen,projection,print" />
        <!-- Override stylesheet -->
        <link href="'.$baseurl.'/css/css_override.php?k=&css_reload_key='.$css_reload_key.'" rel="stylesheet" type="text/css" media="screen,projection,print" />
        <!--- FontAwesome for icons-->
        <link rel="stylesheet" href="'.$baseurl.'/lib/fontawesome/css/all.min.css?css_reload_key='.$css_reload_key.'">
        <link rel="stylesheet" href="'.$baseurl.'/lib/fontawesome/css/v4-shims.min.css?css_reload_key='.$css_reload_key.'">
        </head>
        <body lang="en" class="librarylink-iframe">
        ';
    }

function librarylink_iframe_footer()
    {
        return '</body></html>';
    }

function libraylink_iframe_thumbnail($type,$title,$thm_url,$scr_url,$ref)
    {
        return '
        <div class="ResourcePanel  ArchiveState0  ResourceType1" id="ResourceShell47"     style="height: 274px;">
        <div class="ResourceTypeIcon fa fa-fw fa-'.$type.'" ></div>
            <a class="ImageWrapper"
                href="#"  
                onClick="return ModalLoad(this,true);" 
                title="'.$title.'">                                
                <img border="0" style="margin-top:43px;" src="'.$thm_url.'" alt="'.$title.'" />
            </a>
            <div class="ResourcePanelInfo AnnotationInfo">&nbsp;</div>
            <div class="ResourcePanelInfo ResourceTypeField8">
                <a href="#"  
                onClick="return ModalLoad(this,true);" 
                title="'.$title.'">
                '.$title.'</a>&nbsp;
            </div>
            <div class="clearer"></div>
            <div class="ResourcePanelIcons">
                &nbsp;       
                <!-- Preview icon -->
                <span class="IconPreview"><a aria-hidden="true" class="fa fa-expand" id="previewlinkcollection'.$ref.'" href="#" title="Full screen preview"></a></span>
                <script>
                    jQuery(document).ready(function() {
                    jQuery("#previewlinkcollection'.$ref.'")
                        .attr("href", "'.$scr_url.'")
                        .attr("data-title", "'.$title.'")
                        .attr("data-lightbox", "lightboxcollection")
                        .attr("onmouseup", "closeModalOnLightBoxEnable();");
                    });
                 </script>
        
            <div class="clearer"></div>
        </div>  
        </div>        
        ';
    }

function librarylink_iframe_vrview($title, $type, $url, $preview) 
    {
    global $baseurl,$css_reload_key;
    if($type!='image' and $type!='video') return '';
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.' - VR view</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
    html, body {
        height: 100%;
        width: 100%;
        margin: 0px;
        text-align: center;
    }
    .container {
        height: 100%;
        width: 100%;
        border: 1px solid black;
    }
    iframe { width: 100%; height: 100%; }
    </style>
    <script src="'.$baseurl.'/vrview/build/vrview.min.js"></script>
</head>    
<body>
    <h3>'.$title.'</h3>
    <div id="vrview" class="container"></div>
    <script>
    window.addEventListener("load", function() {
        var vrView = new VRView.Player("#vrview", {
            '.$type.': "'.$url.'",
            preview: "'.$preview.'"
        });
    });
    </script>
</body>    
</html>
    ';
    }

function librarylink_iframe_video($title,$url)
    {
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.'</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
        .video {
            max-height: 400px;
        }
    </style>
</head>    
<body>
    <center>
    <h3>'.$title.'</h3>
    <video class="video" controls>
    <source src="'.$url.'" type="video/mp4">
    Your browser does not support the video tag.
    </video>
    </center>
</body>    
</html>
    ';
    }

function librarylink_iframe_image($title,$url)
    {
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.'</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
        img {
            max-height: 400px;
        }
    </style>
</head>    
<body>
    <center>  
    <h3>'.$title.'</h3>
    <img src="'.$url.'" alt="'.$title.'" title="'.$title.'">
    </center>
</body>    
</html>
    ';
    }

function librarylink_iframe_audio($title,$url,$preview)
    {
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.'</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
        body {
            background-image: url("'.$preview.'");
            background-repeat: no-repeat;
            background-size: auto 100%;
            background-position: center bottom;
            min-height:400px;
        }
    </style>
</head>    
<body>
    <center>
    <h3>'.$title.'</h3>
    <audio controls>
    <source src="'.$url.'">
    Your browser does not support the audio tag.
    </audio>
    </center>
</body>    
</html>
    ';
    }