<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkCollectionsPrechangecollection()
    {
    librarylink_create_librarylink_collection();
    return true;
    }

function HookLibrarylinkCollectionsThumbsmenu()
    {
    //print "<p>Thumbsmenu</p>";
    return false;
    }

function HookLibrarylinkCollectionsBeforecollectiontoolscolumn()
    {
    global $usercollection,$librarylink_collection_selected, $k,$change_col_url,$collection_dropdown_user_access_mode;

    if(isset($_POST['ll_link'])) $ll_link=$_POST['ll_link']; else $ll_link='';

    $librarylink_collection_selected=false;
    if(is_librarylink_collection($usercollection))
        {
        $librarylink_collection_selected=true;
        $records=librarylink_get_all_records();
        if(count($records))
            {
                print "<form method=\"get\" id=\"recselect\">\n";
                print "LibraryLink record:<br />\n";
                if (!checkperm("b"))
                    { 
                    $onchange=sprintf("ChangeCollection(jQuery(this).val(),'%s','%s','%s'",urlencode($k),urlencode($usercollection),$change_col_url);
                    } else {
                    $onchange="document.getElementById('colselect').submit();";
                    }
                if ($collection_dropdown_user_access_mode)
                    {
                        $class="SearchWidthExp";
                    } else {
                        $class="SearchWidth";
                    }
                printf("<select name=\"ll_link\" onchange=\"%s\" class=\"%s\">\n",$onchange,$class);
                foreach($records as $r)
                    {
                        printf("<option value=\"%s\" %s>%s</option>\n",$r,$ll_link==$r?'selected':'',$r);
                    }
                print "</select>\n";
                print "</form>\n";
            }
        }
    return true;
    }

function HookLibrarylinkCollectionsPrevent_running_render_actions()
    {
        global $librarylink_collection_selected;
        return $librarylink_collection_selected; //disable actions if in  LibraryLink collection
    }