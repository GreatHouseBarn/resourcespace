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
        $required_pages=array(  //not all pages need our plugin's intervention - only these
            '/pages/ajax/browsebar_load.php',
            '/pages/collection_manage.php', //not sure about this one
            '/pages/collections.php',
            '/pages/search.php',
        );
        if(in_array($_SERVER['SCRIPT_NAME'],$required_pages))
            {
            lldebug("-----------------------------------------------------------");
            lldebug("Afterregisterplugin");
            global $userref, $collection_allow_creation, $links_changed, $baseurl;
            if(!checkperm("LL")) { return true; } //no LibraryLink permissions
            if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
            db_begin_transaction();
            $collection_ids=librarylink_get_linked_collections($userref);   //get this users current linked collections
            librarylink_remove_linked_collections_from_user($userref,$collection_ids); //remove any collections we can see
            $links=librarylink_get_link_parameters();   //get our requested (or cookied) list of records
            if(count($links)>0)
                {
                foreach($links as $link) //make sure each specified record collection exists and is visible to the user
                    {
                    if($collection=librarylink_create_linked_collection($link['xg_type'], $link['xg_key'], $link['label']))
                        {
                        librarylink_add_user_to_linked_collection($collection['ref']);
                        }
                    }
                }
                db_end_transaction();

            if($links_changed)  //show the last record in the search if our record list changed
                {
                if(!isset($_GET['search']) or (isset($_GET['search']) and $_GET['search']=='')) //and an empty search phrase?
                    {
                    $redirect=sprintf("%/search.php?search=!collection%s",$baseurl,$collection['ref']);
                    header('Location: '.$redirect); //redirect to showing the librarylink collection
                    set_user_collection($userref,$collection['ref']);
                    exit;
                    }
                }

            }
        }
    }

function HookLibrarylinkAllAdd_bottom_in_page_nav_left()
    {
    global $librarylink_auto_refresh_collection_top;
    if(isset($_GET['search']))
        {
        $search=$_GET['search'];
        if(preg_match('/^\!collection([0-9]+)/',$search,$m))
            {
            $search_collection=$m[1];
            if(librarylink_is_linked_collection($search_collection))
                {
                print sql_value(sprintf("select description as value from collection where ref=%s",$search_collection),'');
                if($librarylink_auto_refresh_collection_top>0) printf("
                <script>jQuery( document ).ready(function(){setTimeout(function(){UpdateResultOrder();},%s);});</script>\n",$librarylink_auto_refresh_collection_top*1000);
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