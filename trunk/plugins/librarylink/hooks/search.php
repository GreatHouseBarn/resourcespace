<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkSearchMoresearchcriteria()
    {
    global $librarylink_links, $userref, $collection_allow_creation, $baseurl;

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
        if(isset($_REQUEST['ll_links'])) //only empty the collection if we've been given a ll_links URL param
            {
            $collection_description="This is the LibraryLink collection which is currently not holding any linked record resources.\n";
            sql_query(sprintf("update collection set description='%s' where ref=%s",escape_check($collection_description),$collection));
            sql_query("delete from collection_resource where collection='$collection'");
            sql_query("delete from collection_keyword where collection='$collection'");
            lldebug("Collection emptied!");
            }
        } else {
        $collection=create_collection($userref,'LibraryLink',0);
        lldebug("Collection created!");
        }

    $librarylink_links=librarylink_get_links_parameters();
    if(count($librarylink_links)>0)
        {            
        $collection_description="This is the LibraryLink collection which is currently holding resources that are linked to the following records:\n";
        //get the resources only that have ALL the links supplied
        foreach($librarylink_links as $link)
            {
            $resource_ids = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s' order by xgrank asc",escape_check($link['xg_type']),escape_check($link['xg_key'])));
            if(!isset($resources)) $resources=$resource_ids;
            $resources=array_intersect($resource_ids,$resources);
            $collection_description.=sprintf("Record Type: '%s', Record Key: '%s'\n",$link['xg_type'],$link['xg_key']);
            }
            sql_query(sprintf("update collection set description='%s' where ref=%s",escape_check($collection_description),$collection));
        $resources=array_unique($resources);
        lldebug($resources);
        if(count($resources)) //we have some resources linked to the the record(s)
            {
            $col_count=sql_value(sprintf("select count(resource) as value from collection_resource where collection=%s",$collection),0);
            lldebug($col_count);
            if($col_count==0) //if the collection is empty then fill it
                {
                $order=1;
                foreach($resources as $resource) 
                    {                    
                    lldebug("Adding resource: $resource to collection: $collection");
                    sql_query(sprintf("insert into collection_resource(resource,collection,sortorder) values ('%s','%s','%s')",escape_check($resource),escape_check($collection),$order++));
                    }
                }
            }
        if(isset($_REQUEST['ll_links'])) //have links been provided in the URL?
            {
            if(!isset($_GET['search']) or (isset($_GET['search']) and $_GET['search']=='')) //and an empty search phrase?
                {
                $redirect=sprintf("%/search.php?search=!collection%s",$baseurl,$collection);
                header('Location: '.$redirect); //redirect to showing the librarylink collection
                exit;
                }
            }
        }

        return true;
    }
