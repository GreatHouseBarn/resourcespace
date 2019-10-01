<?php
include_once(__DIR__."/../../../include/general.php");
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

function HookLibraryLinkAllInitialise()
    {
    lldebug("-----------------------------------------------------------");
    lldebug("Initialise");
    if(!$iframe_type=librarylink_get_iframe_parameters()) return;
    global $librarylink_iframe_config_override;
    if(@file_exists(__DIR__.'/../config/'.$librarylink_iframe_config_override))
        {
        include_once(__DIR__.'/../config/'.$librarylink_iframe_config_override);
        if(isset($iframe_config[$iframe_type])) //find the override config array with our name
            {
            foreach($iframe_config[$iframe_type] as $k=>$v) //and set these values into global variables
                {
                    global $$k;
                    $$k=$v;
                    lldebug(sprintf("Setting config variable: \$%s to value: '%s'",$k,$v));
                }
            }
        }
    }

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

            } else lldebug("Skipped doing anything for: ".$_SERVER['SCRIPT_NAME']);
        }
    }

function HookLibrarylinkAllAdd_bottom_in_page_nav_left()
    {
    global $librarylink_auto_refresh_collection_top, $lang, $baseurl, $collection_allow_creation;
    if(!checkperm("LL")) { return true; } //no LibraryLink permissions
    if (checkperm("b") || !$collection_allow_creation) { return true; }; //no bottom collection bar or create collection permissions
    lldebug("-----------------------------------------------------------");
    lldebug("Add_bottom_in_page_nav_left");
    $timer=false;
    if(isset($_REQUEST['search']))
        {
        $search=$_REQUEST['search'];
        if(preg_match('/^\!collection([0-9]+)/',$search,$m))
            {
            $search_collection=$m[1];
            if(librarylink_is_linked_collection($search_collection))
                {
                $collection=librarylink_get_linked_collection_by_id($search_collection);
                printf("<div>%s</div>\n",$collection['description']);
                printf("<div>%s</div>\n",sprintf($lang['librarylink_collection_last_updated'],date('D, jS M Y H:i:s',$collection['last_update'])));
                if($librarylink_auto_refresh_collection_top>0)
                    {
                     printf("
                    <script>var ll_s_ctime=%s;
                    var ll_s_timer;
                    clearTimeout(ll_s_timer);
                    function ll_check_s_ctime() {
                        clearTimeout(ll_s_timer);
                        jQuery.get('%s?c=%s',function(d,s) { if(d>ll_s_ctime) { UpdateResultOrder(); } else { ll_s_timer=setTimeout(ll_check_s_ctime,%s); }; });
                    } 
                    ll_s_timer=setTimeout(ll_check_s_ctime,%s);
                    </script>\n",$collection['last_update'],
                    $baseurl.'/plugins/librarylink/ajax.php',
                    $search_collection,
                    $librarylink_auto_refresh_collection_top*1000,
                    $librarylink_auto_refresh_collection_top*1000);
                    $timer=true;
                    }                
                }
            }
        }
    if(!$timer and $librarylink_auto_refresh_collection_top>0) printf("
                    <script>var ll_s_timer;
                    clearTimeout(ll_s_timer);
                    </script>\n");     
    return true;
    }

function HookLibraryLinkAllBeforedeleteresourcefromdb($ref)
    {
        lldebug("-----------------------------------------------------------");
        lldebug("Beforedeleteresourcefromdb");
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