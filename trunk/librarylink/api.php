<?php
include_once("./api/include/snoopy.class.php");

$api_calls=array(
    // Existing ResourceSpace API endpoints are defined here:
    array("api"=>"---ResourceSpace API Functions:---"),
    array("api"=>"do_search","search"=>null,"restypes"=>"","order_by"=>"relevance","archive"=>0,"fetchrows"=>-1,"sort"=>"desc"),
    array("api"=>"search_get_previews","search"=>null,"restypes"=>"","order_by"=>"relevance","archive"=>0,"fetchrows"=>-1,"sort"=>"desc","recent_search_daylimit"=>"","getsizes"=>"","previewext"=>"jpg"),
    array("api"=>"get_resource_field_data","resource"=>null),
    array("api"=>"create_resource","resource_type"=>null,"archive"=>999,"url"=>"","no_exif"=>false,"revert"=>false,"autorotate"=>false,"metadata"=>""),
    array("api"=>"update_field","resource"=>null,"field"=>null,"value"=>null,"nodevalues"=>false),
    array("api"=>"delete_resource","resource"=>null),
    array("api"=>"copy_resource","from"=>null,"resource_type"=>-1),
    array("api"=>"get_resource_log","resource"=>null,"fetchrows"=>-1),
    array("api"=>"update_resource_type","resource"=>null,"type"=>null),
    array("api"=>"get_resource_path","ref"=>null,"getfilepath"=>null,"size"=>"","generate"=>true,"extension"=>"jpg","page"=>1,"watermarked"=>false,"alternative"=>-1),
    array("api"=>"get_resource_data","resource"=>null),
    array("api"=>"get_alternative_files","resource"=>null,"order_by"=>"","sort"=>""),
    array("api"=>"get_resource_types",),
    array("api"=>"add_alternative_file","resource"=>null,"name"=>null,"description"=>'',"file_name"=>'',"file_extension"=>'',"file_size"=>0,"alt_type"=>'',"file"=>''),
    array("api"=>"delete_alternative_file","resource"=>null,"ref"=>null),
    array("api"=>"upload_file","ref"=>null,"no_exif"=>false,"revert"=>false,"autorotate"=>false,"file_path"=>""),
    array("api"=>"upload_file_by_url","ref"=>null,"no_exif"=>false,"revert"=>false,"autorotate"=>false,"url"=>""),
    array("api"=>"get_related_resources","ref"=>null),
    array("api"=>"get_field_options","ref"=>null,"nodeinfo"=>false),
    array("api"=>"get_user_collections",),
    array("api"=>"add_resource_to_collection","resource"=>null,"collection"=>null),
    array("api"=>"remove_resource_from_collection","resource"=>null,"collection"=>null),
    array("api"=>"create_collection","name"=>null),
    array("api"=>"delete_collection","ref"=>null),
    array("api"=>"search_public_collections","search"=>"","order_by"=>"name","sort"=>"ASC","exclude_themes"=>true,"exclude_public"=>false),
    array("api"=>"set_node","ref"=>null,"resource_type_field"=>null,"name"=>null,"parent"=>'',"order_by"=>0,"returnexisting"=>false),
    array("api"=>"add_resource_nodes","resource"=>null,"nodestring"=>null),
    array("api"=>"add_resource_nodes_multi","resources"=>null,"nodestring"=>null),
    array("api"=>"resource_log_last_rows","minref"=>0,"days"=>7,"maxrecords"=>0),
    array("api"=>"---LibraryLink API Functions:---"),
    // Our LibraryLink API extensions are defined here:
    array("api"=>"librarylink_test","ref"=>null),
    array("api"=>"librarylink_add_resource_link","ref"=>null,"xg_type"=>null,"xg_key"=>null,"xg_rank"=>1,"add_keywords"=>"true"),
    array("api"=>"librarylink_delete_resource_link","ref"=>null,"xg_type"=>null,"xg_key"=>null,"delete_keywords"=>"true"),
    array("api"=>"librarylink_modify_resource_link","ref"=>null,"xg_type"=>null,"xg_key"=>null,"xg_rank"=>null),
    array("api"=>"librarylink_delete_links","xg_type"=>null,"xg_key"=>null,"delete_keywords"=>"true"),
    array("api"=>"librarylink_upload_resource","resource_type"=>"1 (Photo)","archive"=>"0 (Active)","no_exif"=>"false","revert"=>"false","autorotate"=>"false","metadata"=>"","userfile"=>null),
    array("api"=>"librarylink_do_search","xg_type"=>"","xg_key"=>"","fetchrows"=>-1,"sort"=>"desc")
);

$private_key="ac79b20c58fed01d354ffa2c85fac227b472ed83634195180e4f5bd573fdecdc"; # <---  From RS user edit page for the user to log in as
$user="api"; # <-- RS username of the user you want to log in as

$api=$_POST['api'];
$param=array();
$p=0;
$name='';
foreach($api_calls as $a) {
    if($a['api']==$api) {
        $name=$api;
        foreach($a as $k=>$v) $param[$p++]=array('name'=>$k,'value'=>$v);
        break;
    }
}

if(isset($_POST['Execute'])) {
    $query='user='.$user.'&function='.$api;
    for($i=1;$i<$p;$i++) {
        if(isset($_POST[$param[$i]['name']])) {
            $value=$_POST[$param[$i]['name']];
            $param[$i]['input']=$value;
            if($value!='' or $param[$i]['value']===null) $query.='&param'.$i.'='.urlencode($value);
         }
    }
    # Sign the query using the private key
    $sign=hash("sha256",$private_key . $query);
    $query.='&sign='.$sign;
    $query=$_SERVER['HTTP_ORIGIN'].'/librarylink/api/?'.$query;

    if(!isset($_FILES['userfile'])) {
        # Make the request.
        $results=file_get_contents($query);
    } else {
        $name=$_FILES['userfile']['name'];
        $dest = dirname(__FILE__).'/upload/'.$name;
        $tmp_name=$_FILES['userfile']['tmp_name'];
        if(file_exists($tmp_name)) {        
            copy($tmp_name, $dest);
            // initialise the curl request
            $request = curl_init($query);
            if (function_exists('curl_file_create')) { // php 5.5+
                $cFile = curl_file_create($dest);
            } else { // 
                $cFile = '@'.$dest.';filename='.$name.';type='. $_FILES['userfile']['type'];
            }
            $post=array('userfile'=>$cFile);
            // send a file
            curl_setopt($request, CURLOPT_POST, true);
            curl_setopt($request, CURLOPT_POSTFIELDS, $post);
            // output the response
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $results=curl_exec($request);
            // close the session
            curl_close($request);
            unlink('./upload/'.$name);
        } else $results="No uploaded file!";
    }
}


?>
<!DOCTYPE html>
<html lang="en">	
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- Load jQuery-->
<script src="../lib/js/jquery-3.3.1.min.js"></script>
<?php print $script; ?>
</head>
<body lang="en" class="api">
<form method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Choose an API function:</legend>
        <select name="api" onclick="this.form.submit();">
        <?php
            for($i=0;$i<count($api_calls);$i++) {
                $a=$api_calls[$i]['api'];
                printf('<option value="%s" %s %s>%s</option>\n',$a,$name==$a?'selected':'',$a[0]=="-"?'disabled':'',$a);
            }
        ?>
        </select>    
    </fieldset>
    <fieldset>
<?php
    if($p>0) {
        printf("<legend>%s</legend>\n",$param[0]['value']);
        print "<p>Parameters:</p>\n";
        print "<table cellspacing=5>\n";
        for($i=1;$i<$p;$i++) {
            printf('<tr><td><label>%s: </label></td><td><input type="%s" name="%s" value="%s" %s></td><td>%s</td></tr>',
                $param[$i]['name'],
                $param[$i]['name']=='userfile'?'file':'text',
                $param[$i]['name'],
                $param[$i]['input'],
                $param[$i]['value']!==null?'':'required',
                $param[$i]['value']!==null?sprintf('Default Value: "%s"',$param[$i]['value']):'* Required'
            );
        }
        print "\n</table>\n";
    }
?>
    </form>
    <br /><br />
    <input type="submit" name="Execute" id="execute" value="Execute">
    </fieldset>
    <fieldset><legend>Query:</legend>
        <textarea name="query" id="query" rows=5 style="width:100%;"><?php echo htmlspecialchars($query); ?></textarea>
    </fieldset>

    <fieldset><legend>Output:</legend>
        <textarea name="output" id="output" rows=20 style="width:100%;"><?php echo htmlspecialchars($results); ?></textarea>
    </fieldset>

</body>
</html>