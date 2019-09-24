<?php
###############################
## ResourceSpace
## Local Configuration Script
###############################

# All custom settings should be entered in this file.
# Options may be copied from config.default.php and configured here.

# MySQL database settings
$mysql_server = 'localhost';
$mysql_username = 'resourcespace';
$mysql_password = 'R350urc3$pac3';
$mysql_db = 'resourcespace';

$mysql_bin_path = '/usr/bin';

# Base URL of the installation
$baseurl = 'https://resourcespace.ateb.co.uk';

# Email settings
$email_notify = 'simon@ateb.co.uk';
$email_from = 'resourcespace@ateb.co.uk';
# Secure keys
$spider_password = '2d14845c5af044e13b1af583f7fcb6a9dfacf61ddd96e68f8ac12fc964dc5850';
$scramble_key = 'f2788ece3dc78eef1c5a41b4f5aded7144030fefc7494fc13353d20521ff5d67';
$api_scramble_key = 'eb7a0ef6ce544adeb4912923881a685864cbfafb19bc0b1bc317726159f889b4';

# Paths
$imagemagick_path = '/usr/bin';
$ghostscript_path = '/usr/bin';
$ffmpeg_path = '/usr/bin';
$exiftool_path = '/usr/bin';
$pdftotext_path = '/usr/bin';
$antiword_path='/usr/bin';
$php_path="/usr/bin";
$unoconv_path="/usr/bin";
$unoconv_extensions=array("ods","xls","doc","docx","odt","odp","html","rtf","txt","ppt","pptx","sxw","sdw","html","psw","rtf","sdw","pdb","bib","txt","ltx","sdd","sda","odg","sdc","potx","key");
$calibre_path="/usr/bin";
$calibre_extensions=array("epub","mobi","lrf","pdb","chm","cbr","cbz");


$debug_log_location="/home/simona/resourcespace/logs/resourcespace.log";
# Suppress SQL information in the debug log?
$suppress_sql_log = true;
$debug_log=false;

#SMTP settings
$use_smtp = true;
$use_phpmailer = false;
$smtp_secure = '';
$smtp_host = 'localhost';
$smtp_port = 25;
 
$homeanim_folder = 'filestore/system/slideshow_1591cd73019489b';

$offline_job_queue=true;
# Delete completed jobs from the queue?
$offline_job_delete_completed=true;
# Array of valid utilities (as used by get_utility_path() function) used to create files used in offline job handlers e.g. create_alt_file. create_download_file. Plugins can extend this
$offline_job_prefixes = array("ffmpeg","im-convert","im-mogrify","ghostscript","im-composite","archiver"); 

# Allow to disable thumbnail generation during batch resource upload from FTP or local folder.
# In addition to this option, a multi-thread thumbnail generation script is available in the batch
# folder (create_previews.php). You can use it as a cron job, or manually.
# Notes:-
#  - This also works for normal uploads (through web browser)
#  - This setting may be overridden if previews are required at upload time e.g. if Google Vision facial recognition is configured with a dependent field
$enable_thumbnail_creation_on_upload = false;

$collections_footer = true;
$collections_delete_empty=true;
$sharing_userlists=true; // enable users to save/select predefined lists of users/groups when sharing collections and resources.

# All user permissions for the dash are revoked and the dash admin can manage a single dash for all users. 
# Only those with admin privileges can modify the dash and this must be done from the Team Centre > Manage all user dash tiles (One dash for all)
$managed_home_dash = true;
# Allows Dash Administrators to have their own dash whilst all other users have the managed dash ($managed_home_dash must be on)
$unmanaged_home_dash_admins = true;
$no_welcometext = true;
/*

New Installation Defaults
-------------------------

The following configuration options are set for new installations only.
This provides a mechanism for enabling new features for new installations without affecting existing installations (as would occur with changes to config.default.php)

*/
                                
// Set imagemagick default for new installs to expect the newer version with the sRGB bug fixed.
$imagemagick_colorspace = "sRGB";

# Experimental ImageMagic optimizations. This will not work for GraphicsMagick.
$imagemagick_mpr=true;

# Set the depth to be passed to mpr command.
$imagemagick_mpr_depth="8";

# Should colour profiles be preserved?
$imagemagick_mpr_preserve_profiles=true;

# If using imagemagick and mpr, specify any metadata profiles to be retained. Default setting good for ensuring copyright info is not stripped which may be required by law
$imagemagick_mpr_preserve_metadata_profiles=array('iptc');

$contact_link=false;

$slideshow_big=true;
$home_slideshow_width=1920;
$home_slideshow_height=1080;

$themes_simple_view=true;
$themes_category_split_pages=true;
$theme_category_levels=8;

$stemming=true;
$case_insensitive_username=true;
$user_pref_user_management_notifications=true;
$themes_show_background_image = true;

$use_zip_extension=true;
$collection_download=true;

$ffmpeg_preview_force = true;
$ffmpeg_preview_extension = 'mp4';
$ffmpeg_preview_options = '-f mp4 -b:v 1200k -b:a 64k -ac 1 -c:v h264 -c:a aac -strict -2';
$ffmpeg_preview_async=true;

$daterange_search = true;
$upload_then_edit = true;
$search_filter_nodes = true;

$exiftool_resolution_calc=true;

# Small icon above thumbnails showing the resource type
$resource_type_icons=true;
# Map the resource type to a font awesome 4 icon
$resource_type_icons_mapping = array(1 => "camera", 2 => "file", 3 => "video-camera", 4 => "music");

# Image preview zoom using jQuery.zoom (hover over the preview image to zoom in on the resource view page)
$image_preview_zoom=true;

$omit_filter_bar_pages = array();

#######################################
########################## Annotations:
#######################################
// Ability to annotate images or documents previews.
// Annotations are linked to nodes, the user needs to specify which field a note is bind to.
$annotate_enabled = true;

// Specify which fields can be used to bind to annotations
$annotate_fields = array(29,73);

// The user can see existing annotations in read-only mode
$annotate_read_only = false;

// When using anonymous users, set to TRUE to allow anonymous users to add/ edit/ delete annotations
$annotate_crud_anonymous = false;

#######################################
################################  IIIF:
#######################################
// Enable IIIF interface. See http://iiif.io for information on the IIIF standard
$iiif_enabled = true;
$iiif_userid = 3;
$iiif_identifier_field = 29;
$iiif_sequence_field = 1;
$iiif_custom_sizes = true;
$iiif_max_width  = 1024;
$iiif_max_height = 1024;
$preview_tiles = false;
$preview_tiles_create_auto = true;
$preview_tile_size = 1024;
$preview_tile_scale_factors = array(1,2,4,8,16);
$hide_real_filepath = false;

#######################################
################### Facial recognition:
#######################################
// Requires OpenCV and Python (version 2.7.6)
// Credit to “AT&T Laboratories, Cambridge” for their database of faces during initial testing phase.
$facial_recognition = true;

// Set the field that will be used to store the name of the person suggested/ detected
// IMPORTANT: the field type MUST be dynamic keyword list
$facial_recognition_tag_field = 29;

// Physical file path to FaceRecognizer model state(s) and data
// Security note: it is best to place it outside of web root
// IMPORTANT: ResourceSpace will not create this folder if it doesn't exist
$facial_recognition_face_recognizer_models_location = '/home/simona/resourcespace/face_recognition';
#######################################
#######################################

// $geo_override_options = "
// OpenLayers.Lang.setCode(\"en\");
// OpenLayers.ImgPath=\"${baseurl}/lib/OpenLayers/img/\";
// map = new OpenLayers.Map(\"map_canvas\");
// var osm = new OpenLayers.Layer.OSM(\"OpenStreetMap Contoured\",
//   \"https://a.osm.esdm.co.uk/osm/900913/c/\${z}/\${x}/\${y}.png\",
//   {transitionEffect: 'resize'}
// );
// map.addLayers([osm]);
// map.addControl(new openLayers.Control.LayerSwitcher());
// ";

$enable_related_resources=true;

# Adds an option to the upload page which allows Resources Uploaded together to all be related 
/* requires $enable_related_resources=true */
/* $php_path MUST BE SET */
$relate_on_upload=true;

# Option to make relating all resources at upload the default option if $relate_on_upload is set
$relate_on_upload_default=false;

#Size of the related resource previews on the resource page. Usually requires some restyling (#RelatedResources .CollectionPanelShell)
#Takes the preview code such as "col","thm"
$related_resource_preview_size="col";


# Create file checksums?
$file_checksums=true;

# Calculate checksums on first 50k and size if true or on the full file if false
$file_checksums_50k = true;

# Block duplicate files based on checksums? (has performance impact). May not work reliably with $file_checksums_offline=true unless checksum script is run frequently. 
$file_upload_block_duplicates=true;

# checksums will not be generated in realtime; a background cron job must be used
# recommended if files are large, since the checksums can take time
$file_checksums_offline = true;



$use_pdfjs_viewer = true;