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
                if($pagename!='') fwrite($fp,"-----------------------------------------------------------------$pagename\n");
                fwrite($fp,"Hook Name: $name\n");
                fwrite($fp,"Page Name: $pagename\n");
                //fwrite($fp,"Params: ".print_r($params,1)."\n");
                fclose($fp);
                }
            return true;
        }
    }

function HookLibrarylinkAllAdd_bottom_in_page_nav_left()
    {
    //print "<p>Hello World - test librarylink hook</p>";
    //librarylink_create_librarylink_collection();
    return true;
    }

function HookLibraryLinkAllBeforedeleteresourcefromdb($ref)
    {
        lldebug("Hook: Beforedeleteresourcefromdb was called.");
        librarylink_delete_links_by_ref($ref, false);
        return true;
    }