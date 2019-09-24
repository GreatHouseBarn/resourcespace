<?php
include_once(__DIR__."/../../../include/collections_functions.php");
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

global $librarylink_hook_debug_enable;
if($librarylink_hook_debug_enable and !function_exists('hook_modifier'))
    {
    function hook_modifier($name, $pagename, $params)
        {
            global $librarylink_hook_debug_file;
            if($fp=fopen($librarylink_hook_debug_file,'a'))
                {
                //if($pagename!='') fwrite($fp,"-----------------------------------------------------------------$pagename\n");
                fwrite($fp,"Hook Name: $name ");
                fwrite($fp,"Page Name: $pagename\n");
                //fwrite($fp,"Params: ".print_r($params,1)."\n");
                fclose($fp);
                }
            return true;
        }
    }

// function HookLibrarylinkAllHandleuserref($params)
//     {
//     lldebug("Handleuserref");
//     lldebug($params);
//     }

function HookLibrarylinkAllAfterregisterplugin($params='')
    {
    if($params=='librarylink')
        {
        lldebug("-----------------------------------------------------------");
        lldebug("Afterregisterplugin");
        global $userref, $collection_allow_creation, $links_changed, $baseurl;
        if(!checkperm("LL")) { return true; } //no LibraryLink permissions
        if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
        db_begin_transaction();
        $collection_ids=librarylink_get_linked_collections();
        librarylink_remove_linked_collections_from_user($userref,$collection_ids); //remove any collection we can see
        $links=librarylink_get_link_parameters();
        if(count($links)>0)
            {
            foreach($links as $link) //make sure each specified collection exists and is visible to the user
                {
                if(!$collection_id=librarylink_get_linked_collection($link['xg_type'], $link['xg_key']))
                    {
                    $collection_id=librarylink_create_linked_collection($link['xg_type'], $link['xg_key'], $link['label']);
                    }
                if($collection_id) { librarylink_add_user_to_linked_collection($collection_id); }
                }
            }
            db_end_transaction();

        if($links_changed)
            {
            if(!isset($_GET['search']) or (isset($_GET['search']) and $_GET['search']=='')) //and an empty search phrase?
                {
                $redirect=sprintf("%/search.php?search=!collection%s",$baseurl,$collection_id);
                header('Location: '.$redirect); //redirect to showing the librarylink collection
                exit;
                }
            }

        }
    }

function HookLibrarylinkAllAdd_bottom_in_page_nav_left()
    {
    global $librarylink_links,$librarylink_auto_refresh_collection_top;
    if(isset($_GET['search']))
        {
        $search=$_GET['search'];
        if(preg_match('/^\!collection([0-9]+)/',$search,$m))
            {
            $search_collection=$m[1];
            if(is_librarylink_collection($search_collection))
                {
                print nl2br(sql_value(sprintf("select description as value from collection where ref=%s",$search_collection),''));
                if($librarylink_auto_refresh_collection_top) print "
                <script>setTimeout(function(){UpdateResultOrder();},20000);</script>\n";
                }
            }
        }
    return true;
    }

function HookLibraryLinkAllBeforedeleteresourcefromdb($ref)
    {
        lldebug("Hook: Beforedeleteresourcefromdb was called.");
        librarylink_delete_links_by_ref($ref, false);
        return true;
    }

function HookLibrarylinkAllBefore_footer_always()
    {
        print "<div class=\"ll_footer\">LibraryLink is powered by ResourceSpace</div>";
    }

function HookLibraryLinkAllModified_collections($params)
    {
        lldebug("Modified_collections");
        lldebug($params);
    }