<?php
/*
 * API v1 : Bindings to built in functions
 *
 * Library Link API functions
 */

function api_librarylink_test($ref)
    {
        lldebug("LibraryLink Test - $ref");
        return array("LibraryLink Test - $ref", $_REQUEST);
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

function api_librarylink_delete_links_by_ref($ref, $delete_keywords = true)
    {
    return librarylink_delete_links_by_ref($ref, $delete_keywords);
    }

function api_librarylink_upload_resource($resource_type,$archive=0,$no_exif=false,$revert=false,$autorotate=false,$metadata="")
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

function api_librarylink_get_all_links()
    {
    return librarylink_get_all_links();
    }

function api_librarylink_do_search($xg_type="",$xg_key="",$fetchrows=-1,$sort="asc")
    {
    $fetchrows = ($fetchrows > 0 ? $fetchrows : -1);
    # Search capability.
    # Note the subset of the available parameters. We definitely don't want to allow override of permissions or filters.            
    if(!checkperm('s'))
        {
        return array();
        }
        
    $results = librarylink_do_search($xg_type,$xg_key,$fetchrows,$sort);

    if (!is_array($results)) { return array(); }
    
    for ($n = 0; $n < count($results); $n++)
        {
        if (is_array($results[$n]))
            {
            $results[$n] = array_map("i18n_get_translated",$results[$n]);
            }
        }
    return $results;
    }

function api_librarylink_do_search_iframe($xg_type="",$xg_key="",$fetchrows=-1,$sort="asc")
    {
        $fetchrows = ($fetchrows > 0 ? $fetchrows : -1);
        # Search capability.
        # Note the subset of the available parameters. We definitely don't want to allow override of permissions or filters.            
        if(!checkperm('s')) { return $head.'<h3>No permission to search</h3>'.$foot; }
            
        $results = librarylink_do_search($xg_type,$xg_key,$fetchrows,$sort);

        $head = librarylink_iframe_header();
        $foot = librarylink_iframe_footer();
    
        if (!is_array($results)) { return $head.'<h3>No results were found</h3>'.$foot; }

        global $use_watermark, $baseurl, $resource_type_icons, $resource_type_icons_mapping;
        $out='';
        for ($n = 0; $n < count($results); $n++)
            {
            if (is_array($results[$n]))
                {
                $results[$n] = array_map("i18n_get_translated",$results[$n]);
                lldebug($results[$n]);
                if($results[$n]['has_image']==1) {
                    $thm_url = get_resource_path($results[$n]['ref'],false,'thm',true,'jpg',true,1,$use_watermark,$results[$n]['file_modified']);
                    $scr_url = get_resource_path($results[$n]['ref'],false,'scr',true,'jpg',true,1,$use_watermark,$results[$n]['file_modified']);
                } else {
                    $thm_url = sprintf('%s/gfx/no_preview/extension/%s.png',$baseurl,$results[$n]['file_extension']);
                    $scr_url = $thm_url;
                }
                lldebug($thm_url);
                $results[$n]['thm_url'] = $thm_url;
                $results[$n]['scr_url'] = $scr_url;
                if($resource_type_icons) $type=$resource_type_icons_mapping[$results[$n]['resource_type']]; else $type='';
                $out.=libraylink_iframe_thumbnail($type,$results[$n]['field8'],$thm_url,$scr_url,$results[$n]['ref']);
                }
            }
        return $head.$out.$foot;
    }

function api_librarylink_get_resource_iframe($ref)
    {
        global $use_watermark,$baseurl;
        $head = librarylink_iframe_header();
        $foot = librarylink_iframe_footer();
        $out='';      
        if(!checkperm('s')) { return $head.'<h3>No permission to search</h3>'.$foot; }   
        if(!preg_match('/^[0-9]+$/',$ref)) { return $head.'<h3>Not a valid resource ref</h3>'.$foot; }
        $resource=get_resource_data($ref, true);
        if(!is_array($resource)) { return $head.'<h3>A resource with that ref was not found</h3>'.$foot; }
        //lldebug($resource);
        $type=$resource['resource_type'];
        //lldebug($url);
        $title=trim($resource['field8']);
        if($title=='') $title='Untitled';
        $data=get_resource_field_data($resource['ref']);
        $projectiontype='';
        foreach($data as $d) {
            if($d['name']=='projectiontype') {
                $projectiontype=$d['value'];
                break;
            }
        }
        switch($type) {
            case 1: //photo
                $url=get_resource_path($resource['ref'],false,'',true,'',true,1,false);
                if($resource['has_image']==1) $preview=get_resource_path($resource['ref'],false,'pre',true,'jpg',true,1,$use_watermark,$resource['file_modified']);
                else $preview=sprintf('%s/gfx/no_preview/extension/%s.png',$baseurl,$resource['file_extension']);
                if($projectiontype=='equirectangular') $out=librarylink_iframe_vrview($title,'image',$url,$preview);
                else $out=librarylink_iframe_image($title,$url);
                break;
            case 2: //document
                if($resource['has_image']==1) $preview=get_resource_path($resource['ref'],false,'pre',true,'jpg',true,1,$use_watermark,$resource['file_modified']);
                else $preview=sprintf('%s/gfx/no_preview/extension/%s.png',$baseurl,$resource['file_extension']);
                $out=librarylink_iframe_image($title,$preview);
                break;
            case 3: //video
                $url = get_resource_path($resource['ref'],false,'',true,'mp4',true,1,false);
                if($resource['has_image']==1) $preview = get_resource_path($resource['ref'],false,'pre',true,'jpg',true,1,$use_watermark,$resource['file_modified']);
                else $preview=sprintf('%s/gfx/no_preview/extension/%s.png',$baseurl,$resource['file_extension']);
                if($projectiontype=='equirectangular') $out=librarylink_iframe_vrview($title,'video',$url,$preview);
                else $out=librarylink_iframe_video($title,$url);
                break;
            case 4: //audio
                $url = get_resource_path($resource['ref'],false,'',true,'mp3',true,1,false);
                $out=librarylink_iframe_audio($title,$url);
                break;
        }

        return $out; 

    }