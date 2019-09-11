<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkCollectionsPrechangecollection()
    {
    //librarylink_create_librarylink_collection();
    return true;
    }

function HookLibrarylinkCollectionsThumbsmenu()
    {
    //print "<p>Thumbsmenu</p>";
    return false;
    }

function HookLibrarylinkCollectionsBeforecollectiontoolscolumn()
    {
    global $usercollection,$librarylink_collection_selected;
    
    $librarylink_collection_selected=false;
    if(is_librarylink_collection($usercollection))
        {
        $librarylink_collection_selected=true;
        $links=librarylink_get_links_parameters(false);
        if(count($links)>0)
            {
            print "
            <script>
            function ChangeLLCollection(collection,k,last_collection,searchParams) {
                console.log(\"changecollection\");
                if(typeof last_collection == 'undefined'){last_collection='';}
                if(typeof searchParams == 'undefined') {searchParams='';}
                thumbs = getCookie(\"thumbs\");
                PopCollection(thumbs);
                // Set the collection and update the count display
                CollectionDivLoad(baseurl_short + 'pages/collections.php?collection=' + collection + '&thumbs=' + thumbs + '&last_collection=' + last_collection + '&k=' + k + '&ll_save=true&' +searchParams );
            }
            </script>
            ";
            if(count($links)==1)
                {
                print "Linking to 1 Record:";
                printf("<br />%s /%s<br />\n",$links[0]['xg_type'],$links[0]['xg_key']);
                } else {
                printf("Linking to %s Records:",count($links));
                print "<select name=\"ll_link\" readonly>\n";
                foreach($links as $link)
                    {
                    $value=sprintf("%s / %s\n",$link['xg_type'],$link['xg_key']);
                    printf("<option value=\"%s\" readonly>%s</option>\n",$value,$value);
                    }
                print "</select>\n";
                }            
                printf("<input type=\"submit\" name=\"ll_save\" value=\"Save Links\" onclick=\"ChangeLLCollection(%s, '');\">\n",$usercollection);
            }
        return false;
        }
    return true;
    }

function HookLibrarylinkCollectionsPrevent_running_render_actions()
    {
    global $librarylink_collection_selected;
    return $librarylink_collection_selected; //disable actions if in  LibraryLink collection
    }

function HookLibrarylinkCollectionsPostchangecollection()
    {
    global $usercollection,$librarylink_collection_selected;
    lldebug("Postchangecollection:".$usercollection);
    if(isset($_REQUEST['ll_save']))
        {
        if(is_librarylink_collection($usercollection))
            {
            $col_resource_ids=get_collection_resources($usercollection);
            $col_resource_ids=array_reverse($col_resource_ids);
            $links=librarylink_get_links_parameters(false);
            $links_count=count($links);
            if($links_count>0)
                {
                //build a tally of resources that used to have ALL of the record links
                for($i=0;$i<$links_count;$i++)
                    {
                    $resource_ids[$i] = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s'",escape_check($links[$i]['xg_type']),escape_check($links[$i]['xg_key'])));
                    foreach($resource_ids[$i] as $ref)
                        {
                        if(!isset($resource_has_links_count[$ref]))
                            {
                            $resource_has_links_count[$ref]=1;
                            } else {
                            $resource_has_links_count[$ref]++;
                            }                       

                        if(!in_array($ref,$col_resource_ids))
                            { 
                             if(!isset($resource_had_links_count[$ref]))
                                {
                                $resource_had_links_count[$ref]=1;
                                } else {
                                $resource_had_links_count[$ref]++;
                                }
                            }
                        }
                    }
                //now lets delete the links from those records that had ALL of the record links
                for($i=0;$i<$links_count;$i++)
                    {
                    foreach($resource_ids[$i] as $ref)
                        {
                        if(isset($resource_has_links_count[$ref]) and $resource_has_links_count[$ref]==$links_count)
                            {
                            lldebug("Removing ".$links[$i]['xg_type']." / ".$links[$i]['xg_key']." from resource: $ref");
                            librarylink_delete_resource_link($ref, $links[$i]['xg_type'], $links[$i]['xg_key'], true);
                            }
                        }                            
                    }
                //now add ALL links to the resources we now have in the collection, and make the ranks highest
                foreach($col_resource_ids as $ref)
                    {
                    foreach($links as $link)
                        {
                        lldebug("Adding ".$link['xg_type']." / ".$link['xg_key']." to resource: $ref");          
                        librarylink_add_resource_link($ref, $link['xg_type'], $link['xg_key'], 1, true);
                        }
                    }

                } //no links
            } //not the ll collection
        } //not saving ll collection
    return true;
    }

