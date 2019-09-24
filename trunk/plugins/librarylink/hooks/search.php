<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

// function HookLibrarylinkSearchMoresearchcriteria()
//     {
//     global $librarylink_links, $userref, $collection_allow_creation, $baseurl;
//     lldebug("Moresearchcriteria(");
//     if(!checkperm("LL")) { return true; } //no LibraryLink permissions
//     if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
//     $links=librarylink_get_link_parameters();
//     if(count($links)>0)
//         {
//         foreach($links as $link) //make sure each collection exists and is visible to the user
//             {
//             if(!$collection_id=librarylink_get_linked_collection($link['xg_type'], $link['xg_key']))
//                 {
//                 $collection_id=librarylink_create_linked_collection($link['xg_type'], $link['xg_key'], $link['label']);
//                 lldebug("Created collection with id: $collection_id");
//                 }
//             if($collection_id) { librarylink_add_user_to_linked_collection($collection_id); }
//             }
//         }
//     }

/*
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
        //only empty the collection if we've been given a ll_links URL param
        if(isset($_REQUEST['ll_links'])) { librarylink_empty_librarylink_collection($collection); }
        } else {
        $collection=librarylink_create_librarylink_collection();
        }

    //get our request or cookie ll_links
    $links=librarylink_get_links_parameters();
    if(count($links)>0)
        {            
        $collection_description="This is the LibraryLink collection which is currently holding resources that are linked to the following records:\n";
        //get the resources only that have ALL the links supplied
        foreach($links as $link)
            {
            $resource_ids = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s' order by xgrank asc",escape_check($link['xg_type']),escape_check($link['xg_key'])));
            if(!isset($resources)) $resources=$resource_ids;
            $resources=array_intersect($resource_ids,$resources);
            $collection_description.=sprintf("Record Type: '%s', Record Key: '%s'\n",$link['xg_type'],$link['xg_key']);
            }
        sql_query(sprintf("update collection set description='%s' where ref=%s",escape_check($collection_description),$collection));
        $resources=array_unique($resources);
        lldebug("Moresearchcriteria:");
        // lldebug($resources);
        // lldebug($_REQUEST);
        if(count($resources)) //we have some resources linked to the the record(s)
            {
            $col_res_ids=sql_array(sprintf("select resource as value from collection_resource where collection=%s",$collection));
            $intersect=array_intersect($resources,$col_res_ids);
            if(count($intersect)!=count($resources) || count($intersect)!=count($col_res_ids))
                {
                    librarylink_empty_librarylink_collection($collection);
                    $order=1;
                    foreach($resources as $resource) 
                        {                    
                        lldebug("Adding resource: $resource to collection: $collection");
                        sql_query(sprintf("insert into collection_resource(resource,collection,sortorder) values ('%s','%s','%s')",escape_check($resource),escape_check($collection),$order++));
                        }
                }
            // $col_count=sql_value(sprintf("select count(resource) as value from collection_resource where collection=%s",$collection),0);
            // lldebug($col_count);
            // if($col_count==0) //if the collection is empty then fill it
            //     {
            //     $order=1;
            //     foreach($resources as $resource) 
            //         {                    
            //         lldebug("Adding resource: $resource to collection: $collection");
            //         sql_query(sprintf("insert into collection_resource(resource,collection,sortorder) values ('%s','%s','%s')",escape_check($resource),escape_check($collection),$order++));
            //         }
            //     }
            }


        if (checkperm("h"))
            {
            $reorder=getvalescaped("reorder",false);
            if ($reorder)
                {
                $neworder=json_decode(getvalescaped("order",false));
                // lldebug($col_resource_ids,'collection');
                // lldebug($neworder,'collection new order');
                $diff=array();
                $col_resource_ids=get_collection_resources($collection);
                for($i=0;$i<count($neworder);$i++)
                    {
                    if($col_resource_ids[$i]!=$neworder[$i]) $diff[]=$neworder[$i];
                    }
                // lldebug($diff,'diff');
                if(count($links)>0)
                    {
                    foreach($links as $link)
                        {
                        $resources=librarylink_get_ranks($link['xg_type'], $link['xg_key']);
                        foreach($resources as $ref=>$rank) { if(!in_array($ref,$diff)) unset($resources[$ref]); }
                        lldebug($resources,'resource existing ranks');
                        $newranks=array();
                        $i=0;
                        foreach($resources as $ref=>$rank) 
                            {
                            $newranks[$diff[$i]]=$rank; 
                            $id=librarylink_modify_resource_link($diff[$i++], $link['xg_type'], $link['xg_key'], $rank);
                            // lldebug($id);
                            }
                        lldebug($newranks,'resource new ranks');
                        }
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
*/

function HookLibrarylinkSearchbeforereturnresults($params)
    {
    //lldebug("BeforeSearchResults:");
    //lldebug($params);
    //lldebug($_REQUEST);
    }