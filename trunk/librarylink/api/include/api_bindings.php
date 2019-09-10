<?php
/*
 * API v1 : Bindings to built in functions
 *
 * Library Link API functions
 */

function api_librarylink_test($ref)
    {
        lldebug("LibraryLink Test - $ref");
        return "LibraryLink Test - $ref";
    }


function api_librarylink_add_resource_link($ref, $xg_type, $xg_key, $xg_rank=1, $add_keywords = true)
    {
    return librarylink_add_resource_link($ref, $xg_type, $xg_key, $xg_rank, $add_keywords);
    }

function api_librarylink_delete_resource_link($ref, $xg_type, $xg_key, $delete_keywords = true)
    {
    return librarylink_delete_resource_link($ref, $xg_type, $xg_key, $delete_keywords);
    }

function api_librarylink_modify_resource_link($ref, $xg_type, $xg_key, $xg_rank)
    {
    return librarylink_modify_resource_link($ref, $xg_type, $xg_key, $xg_rank);
    }

function api_librarylink_delete_links($xg_type, $xg_key, $delete_keywords = true)
    {
    return librarylink_delete_links($xg_type, $xg_key, $delete_keywords);
    }

function api_librarylink_upload_resource($resource_type,$archive=999,$no_exif=false,$revert=false,$autorotate=false,$metadata="")
    {
        if (!(checkperm("c") || checkperm("d")) || checkperm("XU" . $resource_type))
            {
            return false;
            }

        $no_exif    = filter_var($no_exif, FILTER_VALIDATE_BOOLEAN);
        $revert     = filter_var($revert, FILTER_VALIDATE_BOOLEAN);
        $autorotate = filter_var($autorotate, FILTER_VALIDATE_BOOLEAN);

        # Create a new resource
        $ref=create_resource($resource_type,$archive);

   
        # Also allow upload file in the same pass (API specific, to reduce calls)
        if(isset($_FILES['userfile']))
            {     
            global $filename_field;
            update_field($ref,$filename_field,$_FILES['userfile']['name']);
            $return=upload_file($ref, $no_exif, $revert, $autorotate, "", false);
            if ($return===false) {return false;}
            }
        
        # Also allow metadata to be passed here.
        if ($metadata!="")
            {
            $metadata=json_decode($metadata);
            foreach ($metadata as $field=>$value)
                {
                update_field($ref,$field,$value);
                }
            }
    
        return $ref;
    }