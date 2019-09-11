<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkSearchMoresearchcriteria()
    {
    global $xg_type, $xg_key, $userref, $collection_allow_creation;

    if (checkperm("b") || !$collection_allow_creation) { return true; };
    $collections=get_user_collections($userref);
    $collection=0;
    for($i=0;$i<count($collections);$i++)
        {
            if($collections[$i]['name']==='LibraryLink')
                {
                    $collection=$collections[$i]['ref'];
                    break;
                }
        }
    
    if($collection>0)
        {
        sql_query("delete from collection_resource where collection='$collection'");
        sql_query("delete from collection_keyword where collection='$collection'");
        } else {
        $collection=create_collection($userref,'LibraryLink',0);
        }

    $links=librarylink_get_links_parameters();
    if(count($links)>0)
        {
        //get the resources only that have ALL the links supplied
        foreach($links as $link)
            {
            $resource_ids = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s' order by xgrank asc",escape_check($link['xg_type']),escape_check($link['xg_key'])));
            if(!isset($resources)) $resources=$resource_ids;
            $resources=array_intersect($resource_ids,$resources);
            }
        $resources=array_unique($resources);
        //lldebug($resources);
        if(count($resources)) 
            { 
                $order=1;
                foreach($resources as $resource) 
                    {
                    lldebug("Adding resource: $resource to collection: $collection");
                    sql_query(sprintf("insert into collection_resource(resource,collection,sortorder) values ('%s','%s','%s')",escape_check($resource),escape_check($collection),$order++));
                    }
            }
        }
        return true;
    }