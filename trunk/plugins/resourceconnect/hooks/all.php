<?php

# Check access keys
function HookResourceconnectAllCheck_access_key($resource,$key)
	{
	# Generate access key and check that the key is correct for this resource.
	global $scramble_key;
	$access_key=md5("resourceconnect" . $scramble_key);

	if ($key!=substr(md5($access_key . $resource),0,10)) {return false;} # Invalid access key. Fall back to user logins.

	global $resourceconnect_user; # Which user to use for remote access?
	$userdata=validate_user("u.ref='$resourceconnect_user'");
	setup_user($userdata[0]);
	
	
	
	# Set that we're being accessed via resourceconnect.
	global $is_resourceconnect;
	$is_resourceconnect=true;

	# Disable collections - not needed when accessed remotely
	global $collections_footer;	
	$collections_footer=false;
	
	# Disable maps
	global $disable_geocoding;
	$disable_geocoding=true;

	return true;
	}

function HookResourceConnectAllInitialise()
	{
	# Work out the current affiliate
	global $lang,$language,$resourceconnect_affiliates,$baseurl,$resourceconnect_selected,$resourceconnect_this,$pagename,$collection;

	# Work out which affiliate this site is
	$resourceconnect_this="";
	for ($n=0;$n<count($resourceconnect_affiliates);$n++)			
		{
		if ($resourceconnect_affiliates[$n]["baseurl"]==$baseurl) {$resourceconnect_this=$n;break;}
		}
	if ($resourceconnect_this==="") {exit($lang["resourceconnect_error-affiliate_not_found"]);}
	
	$resourceconnect_selected=getval("resourceconnect_selected","");
	if ($resourceconnect_selected=="" || !isset($resourceconnect_affiliates[$resourceconnect_selected]))
		{
		# Not yet set, default to this site
		$resourceconnect_selected=$resourceconnect_this;
		}
#	setcookie("resourceconnect_selected",$resourceconnect_selected);
	setcookie("resourceconnect_selected",$resourceconnect_selected,0,"/",'',false,true);
	
	// Language string manipulation to warn on certain pages if necessary, e.g. where collection actions will not include remote assets
	switch ($pagename)
		{
		case "contactsheet_settings":
		ResourceConnectCollectionWarning("contactsheetintrotext",getvalescaped("ref",""));
		break;

		case "edit":
		ResourceConnectCollectionWarning("edit__multiple",getvalescaped("collection",""));
		break;
	
		case "collection_log":
		ResourceConnectCollectionWarning("collection_log__introtext",getvalescaped("ref",""));
		break;
	
		case "collection_edit_previews":
		ResourceConnectCollectionWarning("collection_edit_previews__introtext",getvalescaped("ref",""));
		break;
		
		case "search_disk_usage":
		ResourceConnectCollectionWarning("search_disk_usage__introtext",str_replace("!collection","",getvalescaped("search","")));
		break;
	
		case "collection_download":
		ResourceConnectCollectionWarning("collection_download__introtext",getvalescaped("collection",""));
		break;
		}
	
	
	
	}

function ResourceConnectCollectionWarning($languagestring,$collection)
	{
	global $lang;
	# Are there any remote assets?
	$c=sql_value("select count(*) value from resourceconnect_collection_resources where collection='" . escape_check($collection) . "'",0);
	if ($c>0)
		{
		# Add a warning.
		if (!isset($lang[$languagestring])) {$lang[$languagestring]="";}
		$lang[$languagestring].="<p>" . $lang["resourceconnect_collectionwarning"] . "</p>";
		}	
	}

function HookResourceConnectAllSearchfiltertop()
	{
	global $lang,$language,$resourceconnect_affiliates,$baseurl,$resourceconnect_selected;
	if(!checkperm("resourceconnect"))
        {
        return false;
        }
	   ?>
    <script>
    jQuery(document).ready(function()
        {
        jQuery(document).tooltip();
        });
    </script>
	<div class="Question SearchItem ResourceConnectSearch"><?php echo $lang["resourceconnect_search_database"];?>&nbsp;<a href="#" onClick="styledalert('<?php echo $lang["resourceconnect_search_database"] ?>','<?php echo $lang["resourceconnect_search_info"] ?>');" title="<?php echo $lang["resourceconnect_search_info"] ?>"><i class="fa fa-info-circle"></i></a><br />
	<select class="SearchWidth" name="resourceconnect_selected" onchange="UpdateResultCount();">
	<?php for ($n=0;$n<count($resourceconnect_affiliates);$n++)
		{
		?>
		<option value="<?php echo $n ?>" <?php if ($resourceconnect_selected==$n) { ?>selected<?php } ?>><?php echo i18n_get_translated($resourceconnect_affiliates[$n]["name"]) ?></option>
		<?php		
		}
	?>
	</select>
	</div>
	<?php
	}


function HookResourceConnectAllGenerate_collection_access_key($collection,$k,$userref,$feedback,$email,$access,$expires)
	{
	# When sharing externally, add the external access key to an empty row if the collection is empty, so the key still validates.
	$c=sql_value("select count(*) value from collection_resource where collection='$collection'",0);
	if ($c>0) {return false;} # Contains resources, key already present
	
	sql_query("insert into external_access_keys(resource,access_key,collection,user,request_feedback,email,date,access,expires) values (-1,'$k','$collection','$userref','$feedback','" . escape_check($email) . "',now(),$access," . (($expires=="")?"null":"'" . $expires . "'"). ");");
	
	}

function HookResourceconnectAllGenerateurl($url)
	{
	# Always use complete URLs when accessing a remote system. This ensures the user stays on the affiliate system and doesn't get diverted back to the base system.
	global $baseurl,$baseurl_short,$pagename,$resourceconnect_fullredir_pages;
	
	if (!in_array($pagename,$resourceconnect_fullredir_pages)) {return $url;} # Only fire for certain pages as needed.
	
	# Trim off the short base URL if it's been set.
	if (substr($url,0,strlen($baseurl_short))==$baseurl_short) {$url=substr($url,strlen($baseurl_short));}
																			
	return ($baseurl . "/" . $url);
	}

function HookResourceconnectAllReset_filter_bar()
    {
    global $extra_params;

    if(is_null($extra_params))
        {
        return false;
        };

    $extra_params = array_merge(
        $extra_params,
        array(
            "resourceconnect_selected" => "",
        ));

    return true;
    }

function HookResourceconnectAllFb_modify_fields(array $fields)
    {
    $index = false;
    if(isset($GLOBALS["hook_return_value"]) && is_array($GLOBALS["hook_return_value"]))
        {
        $fields = $GLOBALS["hook_return_value"];
        }

    foreach($fields as $key => $field)
        {
        // At the end of Simple Search fields
        if($field["simple_search"] == 0 && $field["advanced_search"] == 1)
            {
            $index = $key;
            break;
            }
        }

    $GLOBALS["hook_return_value"] = $return = array_merge(
        array_slice($fields, 0, $index),
        array(array(
            "ref" => null,
            "name" => "ResourceConnect-field", # render_search_field() is globaling $fields for display conditions and this field is in between real RS fields
            "simple_search" => 1,
            "advanced_search" => 0,
            "fct_name" => "HookResourceConnectAllSearchfiltertop",
            "fct_args" => array()
        )),
        array_slice($fields, $index, count($fields) - 1, true));

    return $return;
    }