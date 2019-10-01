<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkCollectionsThumbsmenu()
    {
    global $userref,$usercollection;
    $collections=get_user_collections($userref);
    //lldebug($collections);
    $clist=array();
    foreach($collections as $collection) $clist[]=$collection['ref'];
    if(!in_array($usercollection,$clist)) //if we no longer have the collection shared with us then change the collection window to My Collection
        {
        print "<script>jQuery( document ).ready(function() { ChangeCollection(1,''); }); </script>\n";
        //set_user_collection($userref,1); //set to My Collection
        //$usercollection=1;
        }
    return false;
    }

function HookLibrarylinkCollectionsBeforecollectiontoolscolumn()
    {
    global $collection_allow_creation,$lang,$usercollection,$librarylink_collection_selected;
    global $librarylink_auto_refresh_collection_bottom,$baseurl,$librarylink_resource_archive_updated;
    lldebug("-----------------------------------------------------------");
    lldebug("Beforecollectiontoolscolumn");
    if(!checkperm("LL")) { return true; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
    
    $timer=false;
    $librarylink_collection_selected=false;
    if(librarylink_is_linked_collection($usercollection))
        {
        $librarylink_collection_selected=true;
        if($collection=librarylink_get_linked_collection_by_id($usercollection))
            {
            printf('<div class="ll_col_desc">%s</div>',nl2br(sprintf($lang['librarylink_collection_shortdesc'],$collection['xgtype'],$collection['label'],$collection['xgkey'])));
            if($librarylink_resource_archive_updated) printf("<script>jQuery( document ).ready(function(){ setTimeout(function(){alert('%s');},500); });</script>",$lang['librarylink_resource_archive_updated']);
            
            if($librarylink_auto_refresh_collection_bottom>0) 
                {
                printf("
                <script>var ll_c_ctime=%s;
                var ll_c_timer;
                clearTimeout(ll_c_timer);
                function ll_check_c_ctime() {
                    clearTimeout(ll_c_timer);
                    jQuery.get('%s?c=%s',function(d,s) { if(d>ll_c_ctime) { UpdateCollectionDisplay(''); } else { ll_c_timer=setTimeout(ll_check_c_ctime,%s); }; });
                } 
                ll_c_timer=setTimeout(ll_check_c_ctime,%s);
                </script>\n",$collection['last_update'],
                $baseurl.'/plugins/librarylink/ajax.php',
                $usercollection,
                $librarylink_auto_refresh_collection_bottom*1000,
                $librarylink_auto_refresh_collection_bottom*1000);
                $timer=true;
                } 
            }
        return false;
        }
    if(!$timer and $librarylink_auto_refresh_collection_bottom>0) printf("
        <script>var ll_c_timer;
        clearTimeout(ll_c_timer);
        </script>\n");
    return true;
    }


function HookLibrarylinkCollectionsPrevent_running_render_actions()
    {
    global $librarylink_collection_selected;
    //lldebug("Prevent_running_render_actions");
    return $librarylink_collection_selected; //disable actions if in  LibraryLink collection
    }

function HookLibrarylinkCollectionsPreaddtocollection()
    {
    global $collection_allow_creation,$usercollection,$add,$librarylink_resource_archive_updated;
    if(!checkperm("LL")) { return; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return; }; //no bottom collection bar or create collection permissions
    lldebug("-----------------------------------------------------------");
    lldebug(sprintf("Preaddtocollection: %s, resource: %s",$usercollection,$add));
    if(librarylink_is_linked_collection($usercollection))
        {
        $librarylink_resource_archive_updated=librarylink_ensure_resource_active($add);
        }
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

function HookLibrarylinkCollectionsPostchangecollection()
    {
    global $collection_allow_creation,$usercollection;
    if(!checkperm("LL")) { return; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return; }; //no bottom collection bar or create collection permissions
    lldebug("-----------------------------------------------------------");
    lldebug("Postchangecollection:".$usercollection);
    if(librarylink_is_linked_collection($usercollection))
        {        
        $reorder=getvalescaped("reorder",false);
        if ($reorder)
            {
            librarylink_update_collection_timestamp($usercollection);
            }
        }
    }
