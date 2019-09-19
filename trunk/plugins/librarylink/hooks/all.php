<?php
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