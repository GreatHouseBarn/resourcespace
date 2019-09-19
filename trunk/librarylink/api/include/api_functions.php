<?php
/*
 * API v1 : 
 *
 * Library Link API functions
 */

function lldebug($message, $title='')
    {
        global $librarylink_debug_enable,$librarylink_debug_file;
        if($librarylink_debug_enable)
            {
            if($fp=fopen($librarylink_debug_file,'a'))
                {
                if($title!='') fwrite($fp,"$title :");
                if(!is_array($message) and !is_object($message)) fwrite($fp,$message."\n");
                else fwrite($fp,print_r($message,1));
                fclose($fp);
                }
            }
    }

function librarylink_execute_api_call($query,$pretty=true)
    {
    // Execute the specified API function.
    $params=array();parse_str($query,$params);
    if (!array_key_exists("function",$params)) {return false;}
    $function=$params["function"];
    if (!function_exists("api_" . $function)) {return false;}
    
    // $content_type='json';
    // if(isset($params["content-type"]))
    //     {
    //     $content_type=$params["content-type"];
    //     unset($params["content-type"]);
    //     }
    // if(!in_array($content_type,array('json','raw'))) $content_type='json';
    
    // Construct an array of the real params, setting default values as necessary
    $setparams = array();
    $n = 0;    
    $fct = new ReflectionFunction("api_" . $function);
    foreach ($fct->getParameters() as $fparam)
        {
        $paramkey = $n + 1;
        debug ("API Checking for parameter " . $fparam->getName() . " (param" . $paramkey . ")");
        if (array_key_exists("param" . $paramkey,$params) && $params["param" . $paramkey] != "")
            {
            debug ("API " . $fparam->getName() . " -   value has been passed : '" . $params["param" . $paramkey] . "'");
            $setparams[$n] = $params["param" . $paramkey];
            }
        
        elseif ($fparam->isOptional())
            {
            // Set default value if nothing passed e.g. from API test tool
            debug ("API " . $fparam->getName() . " -  setting default value = '" . $fparam->getDefaultValue() . "'");
            $setparams[$n] = $fparam->getDefaultValue();
            }
        else
            {
             // Set as empty
            debug ("API " . $fparam->getName() . " -  setting null value = '" . $fparam->getDefaultValue() . "'");
            $setparams[$n] = "";    
            }
        $n++;
        }
    
    debug("API - calling api_" . $function);
    $result = call_user_func_array("api_" . $function, $setparams);
    if(is_array($result) or is_object($result) or is_bool($result)) $content_type='json'; else $content_type='raw';
    
    if($content_type=='json')
        {
        if($pretty)
            {
                debug("API: json_encode() using JSON_PRETTY_PRINT");
                return json_encode($result,(defined('JSON_PRETTY_PRINT')?JSON_PRETTY_PRINT:0));
            }
        else
            {
                debug("API: json_encode()");
                $json_encoded_result = json_encode($result);

                if(json_last_error() !== JSON_ERROR_NONE)
                    {
                    debug("API: JSON error: " . json_last_error_msg());
                    debug("API: JSON error when \$result = " . print_r($result, true));
                    }

                return $json_encoded_result;
            }
        }

    if($content_type=='raw') return $result;

    }


function librarylink_add_resource_link($ref, $xg_type, $xg_key, $xg_rank, $add_keywords)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
        
        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

        //lldebug($resource);
        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
        if($id===0)
            {
            db_begin_transaction();
            librarylink_update_ranks($xg_type, $xg_key, $xg_rank); //shift ranks up by one where we want to place a rank
            sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,"Move Links Ranks >= $xg_rank up one",escape_check($user),date('Y-m-d H:i:s')));
            sql_query(sprintf("INSERT into librarylink_link (ref,xgtype,xgkey,xgrank,label) values (%s,'%s','%s',%s,'%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,''));
            $id=sql_insert_id();
            sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,'Add Link',escape_check($user),date('Y-m-d H:i:s')));
            db_end_transaction();
            } else {
                return (object)array('error'=>'a record link with that xgtype and xgkey already exists for this resource');
            }

        return $id; //link id
    }

function librarylink_delete_resource_link($ref, $xg_type, $xg_key, $delete_keywords)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        
        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
        if($id===0) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }
        
        sql_query(sprintf("DELETE from librarylink_link where id=%s",$id));
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),0,'Delete Link',escape_check($user),date('Y-m-d H:i:s')));

        return $id; //link id
    }

function librarylink_modify_resource_link($ref, $xg_type, $xg_key, $xg_rank)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }
        if(!preg_match('/^[0-9]+$/',$xg_rank)) { return (object)array('error'=>'xg_rank must be a number'); }
        
        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }
        
        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        $id = (int)sql_value(sprintf("select id as value from librarylink_link where ref=%s and xgtype='%s' and xgkey='%s'",$ref,escape_check($xg_type),escape_check($xg_key)),0);
        if($id===0) { return (object)array('error'=>'the specified resource does not have a record link with that xgtype and xgkey'); }

        db_begin_transaction();
        librarylink_update_ranks($xg_type, $xg_key, $xg_rank); //shift ranks up by one where we want to place a rank
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,"Move Links Ranks >= $xg_rank up one",escape_check($user),date('Y-m-d H:i:s')));
        sql_query(sprintf("UPDATE librarylink_link set xgrank=%s where id=%s",$xg_rank,$id));
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),$xg_rank,'Update Link Rank',escape_check($user),date('Y-m-d H:i:s')));
        db_end_transaction();

        return $id; //link id
    }

function librarylink_delete_links($xg_type, $xg_key, $delete_keywords)
    {
        if($xg_type=="" or $xg_key=="") { return (object)array('error'=>'xg_type and xgkey cannot be empty'); }

        $resource_ids = sql_array(sprintf("SELECT ref as value from librarylink_link where xgtype='%s' and xgkey='%s'",escape_check($xg_type),escape_check($xg_key)));
        if(count($resource_ids)===0) { return (object)array('error'=>'no resources were found with that xgtype and xgkey'); }
        for($i=0;$i<count($resource_ids);$i++) $resource_ids[$i]=(int)$resource_ids[$i]; //cast all result to int

        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        sql_query(sprintf("DELETE from librarylink_link where ref in (%s) and xgtype='%s' and xgkey='%s'",implode(',',$resource_ids),escape_check($xg_type),escape_check($xg_key)));
        foreach($resource_ids as $ref)
            {
                sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,escape_check($xg_type),escape_check($xg_key),0,'Delete Link',escape_check($user),date('Y-m-d H:i:s')));
            }
        
        return $resource_ids; //list of resource ids
    }

    function librarylink_delete_links_by_ref($ref, $delete_keywords)
    {
        if(!preg_match('/^[0-9]+$/',$ref)) { return (object)array('error'=>'resource ref must be a number'); }

        $resource=get_resource_data($ref, true);
        if(!$resource) { return (object)array('error'=>'a resource with that ref could not be found'); }

        global $userref;
        $userinfo=get_user($userref);
        $user=$userinfo["username"] . "-" . $userinfo["fullname"];

        sql_query(sprintf("DELETE from librarylink_link where ref=%s",$ref));
        sql_query(sprintf("INSERT into librarylink_log (ref,xgtype,xgkey,xgrank,operation,user,`time`) values (%s,'%s','%s',%s,'%s','%s','%s')",$ref,'','',0,'Delete all links',escape_check($user),date('Y-m-d H:i:s')));
        
        return true;
    }

function librarylink_get_all_links()
    {
        $records=sql_query("SELECT concat(xgtype,' - ',xgkey) as link, ref from librarylink_link order by xgtype,xgkey,xgrank");
        $result=array();
        if(count($records))
            {
            foreach($records as $r) $result[$r['link']][]=$r['ref'];
            }
        return $result;
    }

function librarylink_do_search($xg_type, $xg_key, $fetchrows, $sort)
    {
        $resources=array();
        if(!preg_match('/^[0-9]+$/',$fetchrows)) $fetchrows=0;
        if(!in_array($sort,array('asc','desc'))) $sort='asc';
        $limit=$fetchrows>0?'limit '.$fetchrows:'';
        $order=' ORDER BY xgrank '.$sort;

        if($xg_type=="" and $xg_key=="") { return $resources; }
        if($xg_type=="") { $links = sql_query(sprintf("SELECT * from librarylink_link where xgkey='%s' %s %s",escape_check($xg_key),$order,$limit)); }
        elseif($xg_key=="") { $links = sql_query(sprintf("SELECT * from librarylink_link where xgtype='%s' %s %s",escape_check($xg_type),$order,$limit)); }
        else $links = sql_query(sprintf("SELECT * from librarylink_link where xgtype='%s' and xgkey='%s' %s %s",escape_check($xg_type),escape_check($xg_key),$order,$limit));

        if(count($links)===0) { return $resources; }
        //lldebug($links);
        foreach($links as $link)
        {
            $resource=get_resource_data($link["ref"], true);
            $resource['xg_type']=$link['xgtype'];
            $resource['xg_key']=$link['xgkey'];
            $resource['xg_rank']=$link['xgrank'];
            //lldebug($resource);
            $resources[]=$resource; 
        }
        return $resources;
    }

function librarylink_get_ranks($xg_type, $xg_key)
    {
        $resources=array();
        if($xg_type=="" or $xg_key=="") { return $resources; }        
        $links = sql_query(sprintf("SELECT ref,xgrank from librarylink_link where xgtype='%s' and xgkey='%s' order by xgrank asc",escape_check($xg_type),escape_check($xg_key)));
        if(count($links)===0) { return $resources; }
        foreach($links as $link)
            {
            $resources[$link['ref']]=$link['xgrank'];
            }
        return $resources;        
    }

function librarylink_update_ranks($xg_type, $xg_key, $xg_rank)
    {
        if(!preg_match('/^[0-9]+$/',$xg_rank)) $xg_rank=1;
        sql_query(sprintf("UPDATE librarylink_link set xgrank=xgrank+1 where xgtype='%s' and xgkey='%s' and xgrank>=%s",escape_check($xg_type),escape_check($xg_key),$xg_rank));
    }

function librarylink_create_librarylink_collection()
    {
    global $userref, $collection_allow_creation, $librarylink_collection_name;
    if (checkperm("b") || !$collection_allow_creation)
        {
        return false;
        }
    $collections=get_user_collections($userref);
    $found=false;
    for($i=0;$i<count($collections);$i++)
        {
            if($collections[$i]['name']===$librarylink_collection_name)
                {
                    $found=true;
                    break;
                }
        }
    
    if(!$found) { $collection=create_collection($userref, $librarylink_collection_name, 0); }
    lldebug("Collection $collection created!");
    return $collection;
    }

function librarylink_empty_librarylink_collection($collection)
    {
        if(!preg_match('/^[0-9]+$/',$collection)) return false;
        $collection_description="This is the LibraryLink collection which is currently not holding any linked record resources.\n";
        sql_query(sprintf("update collection set description='%s' where ref=%s",escape_check($collection_description),$collection));
        sql_query("delete from collection_resource where collection='$collection'");
        sql_query("delete from collection_keyword where collection='$collection'");
        lldebug("Collection: $collection emptied!");
        return true;
    }

function is_librarylink_collection($usercollection)
    {
        global $userref, $collection_allow_creation, $librarylink_collection_name;
        if (checkperm("b") || !$collection_allow_creation) { return false; }
        $collections=get_user_collections($userref);
        for($i=0;$i<count($collections);$i++)
            {
                if($collections[$i]['ref']==$usercollection and $collections[$i]['name']===$librarylink_collection_name) return true;
            }
        return false;       
    }

function librarylink_get_all_records()
    {
        $records=sql_array("SELECT distinct concat(xgtype,' / ',xgkey) as value from librarylink_link order by xgtype,xgkey");
        return $records;
    }

function librarylink_get_links_parameters($set_cookies=true)
    {
        $links=array();
        if(isset($_REQUEST['ll_links'])) $ll_links=$_REQUEST['ll_links']; else $ll_links='';
        if($ll_links!='') 
            {
            $links=explode(',',$ll_links);
            for($i=0;$i<count($links);$i++) {
                if(false===strpos($links[$i],'|')) unset($links[$i]); //throw away invalid link pairs
            }
            $ll_links=implode(',',$links);
            if($set_cookies) rs_setcookie('ll_links',$ll_links,0,"","",false,false);
            } else {
            if(isset($_COOKIE['ll_links'])) $ll_links=$_COOKIE['ll_links']; else $ll_links='';
  
            if($ll_links!='') 
                {
                $links=explode(',',$ll_links);
                for($i=0;$i<count($links);$i++) {
                    if(false===strpos($links[$i],'|')) unset($links[$i]); //throw away invalid link pairs
                }
                $ll_links=implode(',',$links);
                if($set_cookies) rs_setcookie('ll_links',$ll_links,0,"","",false,false);
                }
            }
        sort($links);
        for($i=0;$i<count($links);$i++)
            {
            $tmp=explode('|',$links[$i]);
            $links[$i]=array();
            $links[$i]['xg_type']=trim($tmp[0]);
            $links[$i]['xg_key']=trim($tmp[1]);
            }
        return $links;
    }

function librarylink_iframe_header()
    {
        global $baseurl,$css_reload_key;
        return '<!DOCTYPE html>
        <html lang="en">	
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
        <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- Load jQuery-->
        <script src="'.$baseurl.'/lib/js/jquery-3.3.1.min.js"></script>
        <!-- Structure Stylesheet -->
        <link href="'.$baseurl.'/css/global.css?css_reload_key='.$css_reload_key.'" rel="stylesheet" type="text/css" media="screen,projection,print" />
        <!-- Colour stylesheet -->
        <link href="'.$baseurl.'/css/colour.css?css_reload_key='.$css_reload_key.'" rel="stylesheet" type="text/css" media="screen,projection,print" />
        <!-- Override stylesheet -->
        <link href="'.$baseurl.'/css/css_override.php?k=&css_reload_key='.$css_reload_key.'" rel="stylesheet" type="text/css" media="screen,projection,print" />
        <!--- FontAwesome for icons-->
        <link rel="stylesheet" href="'.$baseurl.'/lib/fontawesome/css/all.min.css?css_reload_key='.$css_reload_key.'">
        <link rel="stylesheet" href="'.$baseurl.'/lib/fontawesome/css/v4-shims.min.css?css_reload_key='.$css_reload_key.'">
        </head>
        <body lang="en" class="librarylink-iframe">
        ';
    }

function librarylink_iframe_footer()
    {
        return '</body></html>';
    }

function libraylink_iframe_thumbnail($type,$title,$thm_url,$scr_url,$ref)
    {
        return '
        <div class="ResourcePanel  ArchiveState0  ResourceType1" id="ResourceShell47"     style="height: 274px;">
        <div class="ResourceTypeIcon fa fa-fw fa-'.$type.'" ></div>
            <a class="ImageWrapper"
                href="#"  
                onClick="return ModalLoad(this,true);" 
                title="'.$title.'">                                
                <img border="0" style="margin-top:43px;" src="'.$thm_url.'" alt="'.$title.'" />
            </a>
            <div class="ResourcePanelInfo AnnotationInfo">&nbsp;</div>
            <div class="ResourcePanelInfo ResourceTypeField8">
                <a href="#"  
                onClick="return ModalLoad(this,true);" 
                title="'.$title.'">
                '.$title.'</a>&nbsp;
            </div>
            <div class="clearer"></div>
            <div class="ResourcePanelIcons">
                &nbsp;       
                <!-- Preview icon -->
                <span class="IconPreview"><a aria-hidden="true" class="fa fa-expand" id="previewlinkcollection'.$ref.'" href="#" title="Full screen preview"></a></span>
                <script>
                    jQuery(document).ready(function() {
                    jQuery("#previewlinkcollection'.$ref.'")
                        .attr("href", "'.$scr_url.'")
                        .attr("data-title", "'.$title.'")
                        .attr("data-lightbox", "lightboxcollection")
                        .attr("onmouseup", "closeModalOnLightBoxEnable();");
                    });
                 </script>
        
            <div class="clearer"></div>
        </div>  
        </div>        
        ';
    }

function librarylink_iframe_vrview($title, $type, $url, $preview) 
    {
    global $baseurl,$css_reload_key;
    if($type!='image' and $type!='video') return '';
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.' - VR view</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
    html, body {
        height: 100%;
        width: 100%;
        margin: 0px;
        text-align: center;
    }
    .container {
        height: 100%;
        width: 100%;
        border: 1px solid black;
    }
    iframe { width: 100%; height: 100%; }
    </style>
    <script src="'.$baseurl.'/vrview/build/vrview.min.js"></script>
</head>    
<body>
    <h3>'.$title.'</h3>
    <div id="vrview" class="container"></div>
    <script>
    window.addEventListener("load", function() {
        var vrView = new VRView.Player("#vrview", {
            '.$type.': "'.$url.'",
            preview: "'.$preview.'"
        });
    });
    </script>
</body>    
</html>
    ';
    }

function librarylink_iframe_video($title,$url)
    {
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.'</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
        .video {
            max-height: 400px;
        }
    </style>
</head>    
<body>
    <center>
    <h3>'.$title.'</h3>
    <video class="video" controls>
    <source src="'.$url.'" type="video/mp4">
    Your browser does not support the video tag.
    </video>
    </center>
</body>    
</html>
    ';
    }

function librarylink_iframe_image($title,$url)
    {
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.'</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
        img {
            max-height: 400px;
        }
    </style>
</head>    
<body>
    <center>  
    <h3>'.$title.'</h3>
    <img src="'.$url.'" alt="'.$title.'" title="'.$title.'">
    </center>
</body>    
</html>
    ';
    }

function librarylink_iframe_audio($title,$url,$preview)
    {
    return '
<!DOCTYPE html>
<html lang="en">    
<head>
    <title>'.$title.'</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <style type="text/css">
        body {
            background-image: url("'.$preview.'");
            background-repeat: no-repeat;
            background-size: auto 100%;
            background-position: center bottom;
            min-height:400px;
        }
    </style>
</head>    
<body>
    <center>
    <h3>'.$title.'</h3>
    <audio controls>
    <source src="'.$url.'">
    Your browser does not support the audio tag.
    </audio>
    </center>
</body>    
</html>
    ';
    }