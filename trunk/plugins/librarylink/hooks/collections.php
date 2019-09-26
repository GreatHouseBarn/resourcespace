<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkCollectionsThumbsmenu()
    {
    //print "<p>Thumbsmenu</p>";
    return false;
    }

function HookLibrarylinkCollectionsBeforecollectiontoolscolumn()
    {
    global $collection_allow_creation,$lang,$usercollection,$librarylink_collection_selected,$librarylink_auto_refresh_collection_bottom;
    lldebug("-----------------------------------------------------------");
    lldebug("Beforecollectiontoolscolumn");
    if(!checkperm("LL")) { return true; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
    $librarylink_collection_selected=false;
    if(librarylink_is_linked_collection($usercollection))
        {
        $librarylink_collection_selected=true;
        if($collection=librarylink_get_linked_collection_by_id($usercollection))
            {
            printf('<div class="ll_col_desc">%s</div>',nl2br(sprintf($lang['librarylink_collection_shortdesc'],$collection['xgtype'],$collection['label'],$collection['xgkey'])));
            }
        if($librarylink_auto_refresh_collection_bottom) printf("
        <script>setTimeout(function(){UpdateCollectionDisplay('');},%s);</script>\n",$librarylink_auto_refresh_collection_bottom*1000); 
        return false;
        }
    return true;
    }


function HookLibrarylinkCollectionsPrevent_running_render_actions()
    {
    global $librarylink_collection_selected;
    //lldebug("Prevent_running_render_actions");
    return $librarylink_collection_selected; //disable actions if in  LibraryLink collection
    }

function HookLibrarylinkCollectionsPostaddtocollection()
    {
    global $collection_allow_creation,$usercollection,$add;
    if(!checkperm("LL")) { return; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return; }; //no bottom collection bar or create collection permissions
    lldebug("-----------------------------------------------------------");
    lldebug(sprintf("Postaddtocollection: %s, resource: %s",$usercollection,$add));
    if(librarylink_is_linked_collection($usercollection))
        {
        librarylink_set_ranks_by_collection_id($usercollection); //make sure ll collections have proper ranking with no NULLs
        librarylink_add_keyword_to_resource($add,$usercollection); //use the xgtype_xglink from the collection           
        }
    }

function HookLibrarylinkCollectionsPostremovefromcollection()
    {
    global $collection_allow_creation,$usercollection,$remove;
    if(!checkperm("LL")) { return; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return; }; //no bottom collection bar or create collection permissions
    lldebug("-----------------------------------------------------------");
    lldebug(sprintf("Postremovefromcollection: %s, resource: %s",$usercollection,$remove));
    if(librarylink_is_linked_collection($usercollection))
        {
        librarylink_remove_keyword_from_resource($remove,$usercollection); //use the xgtype_xglink from the collection
        librarylink_set_ranks_by_collection_id($usercollection); //make sure ll collections have proper ranking with no NULLs
        }
    }


// function HookLibrarylinkCollectionsAftercollectionsrenderactions()
//     {
//     global $librarylink_links, $userref, $collection_allow_creation, $baseurl;
//     lldebug("Aftercollectionsrenderactions");
//     //if(!checkperm("LL")) { return true; } //no LibraryLink permissions
//     //if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
//     $links=librarylink_get_link_parameters(false);
//     lldebug($links);
//     if(count($links)>0)
//         {
//         foreach($links as $link) //make sure each collection exists and is visible to the user
//             {
//             $collection_id=librarylink_get_linked_collection($link['xg_type'], $link['xg_key']);
//             if($collection_id) { librarylink_remove_user_from_linked_collection($collection_id); }
//             }
//         }
//     }


// function HookLibrarylinkCollectionsBeforecollectiontoolscolumn()
//     {
//     global $usercollection,$librarylink_collection_selected,$librarylink_auto_refresh_collection_bottom;
    
//     $librarylink_collection_selected=false;
//     if(is_librarylink_collection($usercollection))
//         {
//         $librarylink_collection_selected=true;
//         $links=librarylink_get_links_parameters(false);
//         if(count($links)>0)
//             {
//             // print "
//             // <script>
//             // function ChangeLLCollection(collection,k,last_collection,searchParams) {
//             //     jQuery('#ll_save').prop('disabled',true);
//             //     console.log(\"changecollection\");
//             //     if(typeof last_collection == 'undefined'){last_collection='';}
//             //     if(typeof searchParams == 'undefined') {searchParams='';}
//             //     thumbs = getCookie(\"thumbs\");
//             //     PopCollection(thumbs);
//             //     // Set the collection and update the count display
//             //     CollectionDivLoad(baseurl_short + 'pages/collections.php?collection=' + collection + '&thumbs=' + thumbs + '&last_collection=' + last_collection + '&k=' + k + '&ll_save=true&' +searchParams );
//             //     setTimeout(function(){ message_poll(); jQuery('#ll_save').prop('disabled',false); },1000);
//             // }
//             // </script>
//             // ";
//             if($librarylink_auto_refresh_collection_bottom) print "
//             <script>setTimeout(function(){UpdateCollectionDisplay('');},20000);</script>\n";
//             if(count($links)==1)
//                 {
//                 print "Linking to 1 Record:";
//                 printf("<br />%s / %s<br />\n",$links[0]['xg_type'],$links[0]['xg_key']);
//                 } else {
//                 printf("Linking to %s Records:",count($links));
//                 print "<select name=\"ll_link\" readonly>\n";
//                 foreach($links as $link)
//                     {
//                     $value=sprintf("%s / %s\n",$link['xg_type'],$link['xg_key']);
//                     printf("<option value=\"%s\" readonly>%s</option>\n",$value,$value);
//                     }
//                 print "</select>\n";
//                 }            
//                 // printf("<input type=\"submit\" name=\"ll_save\" value=\"Save Links\" onclick=\"ChangeLLCollection(%s, '');\">\n",$usercollection);
//             } else print "Not currently linked to any record.";
//         return false;
//         }
//     return true;
//     }


// function HookLibrarylinkCollectionsPostaddtocollection()
//     {
//     global $usercollection,$librarylink_collection_selected,$userref,$add;
//     lldebug("Postaddtocollection:".$usercollection);
//     lldebug("Add resource: $add");
//     if(is_librarylink_collection($usercollection) and $add>0)
//         {
//         $links=librarylink_get_links_parameters(false);
//         if(count($links)>0)
//             {
//             foreach($links as $link)
//                 {
//                 $message="Adding ".$link['xg_type']." / ".$link['xg_key']." to resource: $add";
//                 lldebug($message);          
//                 $id=librarylink_add_resource_link($add, $link['xg_type'], $link['xg_key'], 1, true);
//                 lldebug($id);
//                 $resources=librarylink_get_ranks($link['xg_type'], $link['xg_key']);
//                 lldebug($resources);
//                 }
//             }
//         }
//     }

// function HookLibrarylinkCollectionsPostremovefromcollection()
//     {
//     global $usercollection,$librarylink_collection_selected,$userref,$remove;
//     lldebug("Postremovefromcollection:".$usercollection);
//     lldebug("Remove resource: $remove");
//     if(is_librarylink_collection($usercollection) and $remove>0)
//         {
//         $links=librarylink_get_links_parameters(false);
//         if(count($links)>0)
//             {
//             foreach($links as $link)
//                 {
//                 $message="Removing ".$link['xg_type']." / ".$link['xg_key']." from resource: $remove";
//                 lldebug($message);
//                 librarylink_delete_resource_link($remove, $link['xg_type'], $link['xg_key'], true);
//                 $resources=librarylink_get_ranks($link['xg_type'], $link['xg_key']);
//                 lldebug($resources);

//                 }
//             }
//         }       
//     }


// function HookLibrarylinkCollectionsPrechangecollection()
//     {
//     global $usercollection,$librarylink_links, $userref, $collection_allow_creation, $baseurl;
//     lldebug("Prechangecollection:".$usercollection);
//     if(!checkperm("LL")) { return true; } //no LibraryLink permissions
//     if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
//     $links=librarylink_get_link_parameters(false);
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


// // function HookLibrarylinkCollectionsPrechangecollection()
// //     {
// //     global $usercollection,$librarylink_collection_selected,$userref;
// //     lldebug("Prechangecollection:".$usercollection);
// //     if(is_librarylink_collection($usercollection))
// //         {
// //         $col_resource_ids=get_collection_resources($usercollection);
// //         // $col_resource_ids=array_reverse($col_resource_ids);
// //         lldebug($col_resource_ids);
// //         }
// //     }

// // function HookLibrarylinkCollectionsCollections_thumbs_loaded()
// //     {
// //     global $usercollection,$librarylink_collection_selected,$userref;
// //     lldebug("Collections_thumbs_loaded:".$usercollection);
// //     if(is_librarylink_collection($usercollection))
// //         {
// //         $col_resource_ids=get_collection_resources($usercollection);
// //         // $col_resource_ids=array_reverse($col_resource_ids);
// //         lldebug($col_resource_ids);
// //         }
// //     }

// function HookLibrarylinkCollectionsPostchangecollection()
//     {
//     global $usercollection,$librarylink_collection_selected,$userref;
//     lldebug("Postchangecollection:".$usercollection);
//     if(is_librarylink_collection($usercollection))
//         {
//         $col_resource_ids=get_collection_resources($usercollection);
//         $links=librarylink_get_links_parameters(false);
//         if (checkperm("h"))
//             {
//             $reorder=getvalescaped("reorder",false);
//             if ($reorder)
//                 {
//                 $neworder=json_decode(getvalescaped("order",false));
//                 // lldebug($col_resource_ids,'collection');
//                 // lldebug($neworder,'collection new order');
//                 $diff=array();
//                 for($i=0;$i<count($neworder);$i++)
//                     {
//                     if($col_resource_ids[$i]!=$neworder[$i]) $diff[]=$neworder[$i];
//                     }
//                 // lldebug($diff,'diff');

//                 if(count($links)>0)
//                     {
//                     foreach($links as $link)
//                         {
//                         $resources=librarylink_get_ranks($link['xg_type'], $link['xg_key']);
//                         foreach($resources as $ref=>$rank) { if(!in_array($ref,$diff)) unset($resources[$ref]); }
//                         lldebug($resources,'resource existing ranks');
//                         $newranks=array();
//                         $i=0;
//                         foreach($resources as $ref=>$rank) 
//                             {
//                             $newranks[$diff[$i]]=$rank; 
//                             $id=librarylink_modify_resource_link($diff[$i++], $link['xg_type'], $link['xg_key'], $rank);
//                             // lldebug($id);
//                             }
//                         lldebug($newranks,'resource new ranks');
//                         }
//                     }
//                 }
//             }
//         }


    // if(isset($_REQUEST['ll_save']))
    //     {
    //     if(is_librarylink_collection($usercollection))
    //         {
    //         $col_resource_ids=get_collection_resources($usercollection);
    //         $col_resource_ids=array_reverse($col_resource_ids);
    //         $links=librarylink_get_links_parameters(false);
    //         $links_count=count($links);
    //         $delete_count=0;
    //         $add_count=0;
    //         $messages=array();
    //         if($links_count>0)
    //             {
    //             //build a tally of resources that used to have ALL of the record links
    //             for($i=0;$i<$links_count;$i++)
    //                 {
    //                 $resource_ids[$i] = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s'",escape_check($links[$i]['xg_type']),escape_check($links[$i]['xg_key'])));
    //                 foreach($resource_ids[$i] as $ref)
    //                     {
    //                     if(!isset($resource_has_links_count[$ref]))
    //                         {
    //                         $resource_has_links_count[$ref]=1;
    //                         } else {
    //                         $resource_has_links_count[$ref]++;
    //                         }                       

    //                     if(!in_array($ref,$col_resource_ids))
    //                         { 
    //                          if(!isset($resource_had_links_count[$ref]))
    //                             {
    //                             $resource_had_links_count[$ref]=1;
    //                             } else {
    //                             $resource_had_links_count[$ref]++;
    //                             }
    //                         }
    //                     }
    //                 }
    //             //now lets delete the links from those records that had ALL of the record links
    //             for($i=0;$i<$links_count;$i++)
    //                 {
    //                 foreach($resource_ids[$i] as $ref)
    //                     {
    //                     if(isset($resource_has_links_count[$ref]) and $resource_has_links_count[$ref]==$links_count)
    //                         {
    //                         $message="Removing ".$links[$i]['xg_type']." / ".$links[$i]['xg_key']." from resource: $ref";
    //                         lldebug($message);
    //                         librarylink_delete_resource_link($ref, $links[$i]['xg_type'], $links[$i]['xg_key'], true);
    //                         $messages[]=$message;
    //                         $delete_count++;
    //                         }
    //                     }                            
    //                 }
    //             //now add ALL links to the resources we now have in the collection, and make the ranks highest
    //             foreach($col_resource_ids as $ref)
    //                 {
    //                 foreach($links as $link)
    //                     {
    //                     $message="Adding ".$link['xg_type']." / ".$link['xg_key']." to resource: $ref";
    //                     lldebug($message);          
    //                     librarylink_add_resource_link($ref, $link['xg_type'], $link['xg_key'], 1, true);
    //                     $messages[]=$message;
    //                     $add_count++;
    //                     }
    //                 }
    //             $messages[]=sprintf("%s links were deleted and %s links were added successfully.",$delete_count,$add_count);
    //             $message=sprintf("<div style=\"width:100%%;\">%s</div>",implode("\n",$messages));
    //             message_add(array($userref),$message,'',$userref,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN);

    //             } //no links
    //         } //not the ll collection
    //     } //not saving ll collection
    // return true;
    // }