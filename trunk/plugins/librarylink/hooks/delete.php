<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibraryLinkDeleteCheck_single_delete()
    {
    global $ref;
    lldebug("-----------------------------------------------------------");
    lldebug("Check_single_delete");
    //librarylink_delete_links_by_ref($ref, false);
    return true;
    }

function HookLibraryLinkDeletePageevaluation()
    {
    global $ref,$lang;
    if(!checkperm("LL")) { return true; } //no LibraryLink permissions
    lldebug("-----------------------------------------------------------");
    lldebug("DeletePageevaluation");
    if($collections=librarylink_get_linked_collections_by_resource($ref))
        {   //we're trying to delete a resource that belongs to one or more Librarylink collections
        //lldebug($collections);       
        $collection_list='';
        $cancel=sprintf('<input name="cancel" type="submit" value="&nbsp;&nbsp;%s&nbsp;&nbsp;" onclick="return ModalClose();"/>',$lang["cancel"]);
        foreach($collections as $collection)
            {
            $collection_list.=sprintf('<div class="ll_col_desc">%s</div>',addslashes(sprintf($lang['librarylink_collection_shortdesc'],$collection['xgtype'],$collection['label'],$collection['xgkey'])));
            }
        
        printf("
        <script>jQuery( document ).ready(function() {
            jQuery('.BasicsBox').find('h1').after('<h2 class=\"ll_delete_warning\">%s</h2>%s<br />');
            jQuery('.QuestionSubmit').find('label').after('%s');
        });
        </script>\n",
        sprintf(count($collections)>1?$lang['librarylink_confirm_resource_delete2']:$lang['librarylink_confirm_resource_delete1'],count($collections)),
        $collection_list,
        $cancel);
        }


    }