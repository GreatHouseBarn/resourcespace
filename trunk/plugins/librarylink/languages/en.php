<?php
# English
# Language File for the LibraryLink Plugin
# -------
#
#

$lang['librarylink_collection_name']="%s - %s (LibraryLink)";
$lang['librarylink_collection_shortdesc']="LibraryLink Type: '%s',\nRecord Name: '%s',\nKey: '%s'";
$lang['librarylink_collection_description']="This is a LibraryLink collection which is holding resources that are linked to: Record Type: '%s', Record Name: '%s', Record Key: '%s'";
$lang["librarylink_currentcollection"]="Current LibraryLink collection:";
$lang["librarylink_collection_last_updated"]="This collection was last updated on %s";

# these are copied from core and have had ResourceSpace replaced by LibraryLink

$lang["home__llwelcometitle"]="Welcome to LibraryLink";
$lang["home__llwelcometext"]="LibraryLink for %s";

$lang["softwarebuild"]="? Build"; # E.g. "LibraryLink Build"
$lang["setup-alreadyconfigured"]="Your LibraryLink installation is already configured.  To reconfigure, you may delete <pre>include/config.php</pre> and point your browser to this page again.";
$lang["setup-successdetails"]="Your initial LibraryLink setup is complete.  Be sure to check out 'include/default.config.php' for more configuration options.";
$lang["setup-visitwiki"]='Visit the <a target="_blank" href="https://www.LibraryLink.com/knowledge-base/">LibraryLink Knowledge Base</a> for more information about customizing your installation.';
$lang["setup-welcome"]="Welcome to LibraryLink";
$lang["setup-introtext"]="This configuration script will help you setup LibraryLink.  This process only needs to be completed once. Required items are marked with a <strong>*</strong>";
$lang['setup-if_admin_username']='The username used to connect to LibraryLink. This user will be the first user of the system.';
$lang["setup-rs_initial_configuration"]="LibraryLink: Initial Configuration";
$lang['plugins-headertext'] = 'Plugins extend the functionality of LibraryLink.';
$lang['reportbug']="Prepare bug report for LibraryLink team";
$lang['contact_sheet_footer_copyright'] = '&#0169; LibraryLink. All Rights Reserved.';
#$lang["all__footer"]="Powered by <a target=\"_blank\" href=\"https://www.resourcespace.com/\">ResourceSpace Open Source Digital Asset Management</a>";
$lang["home__help"]="Help and advice to get the most out of LibraryLink.";
$lang["home__restrictedtitle"]="Welcome to LibraryLink [ver]";
$lang["home__welcometitle"]="Welcome to LibraryLink [ver]";
$lang["login__welcomelogin"]="Welcome to LibraryLink, please log in...";
$lang['linkedheaderimgsrc']="Location of the logo image in the header (Defaults to LibraryLink):";

# these are copied from various plugins and have had ResourceSpace replaced by LibraryLink

$lang["tms_link_field_mappings"]="TMS field to LibraryLink field mappings";
$lang["tms_link_resourcespace_field"]="LibraryLink field";
$lang["tms_link_enable_update_script_info"]="Enable script that will automatically update the TMS data whenever the LibraryLink scheduled task (cron_copy_hitcount.php) is run.";
$lang["tms_link_tms_loginid"]="TMS login ID that will be used by LibraryLink to insert records. A TMS account must be created or exist with this ID";
$lang["tms_link_rs_uid_field"] = "LibraryLink UID field";
$lang["simplesaml_username_suffix"]="Suffix to add to created usernames to distinguish them from standard LibraryLink accounts";
$lang['simplesaml_groupmapping'] = "SAML - LibraryLink Group Mapping";
$lang['simplesaml_rsgroup'] = "LibraryLink Group";
$lang['simplesaml_login'] = 'Use SAML credentials to login to LibraryLink? (This is only relevant if above option is enabled)';
$lang['simplesaml_allow_duplicate_email'] ="Allow new accounts to be created if there are existing LibraryLink accounts with the same email address? (this is overridden if email-match is set above and one match is found)";
$lang['simplesaml_multiple_email_match_subject'] ="LibraryLink SAML - conflicting email login attempt";
$lang['simplesaml_authorisation_rules_description'] = 'Enable LibraryLink to be configured with additional local authorisation of users based upon an extra attribute (ie. assertion/ claim) in the response from the IdP. This assertion will be used by the plugin to determine whether the user is allowed to log in to LibraryLink or not.';
$lang['csv_user_import_intro'] = 'Use this feature to feature to import a batch of users to LibraryLink. Please pay close attention to the format of your CSV file and follow the below standards:';
# Language File for the LibraryLink YouTube Plugin
$lang["youtube_publish_mappings_title"]="LibraryLink - YouTube field mappings";
$lang["youtube_publish_oauth2_advice"]="<p><strong>YouTube OAuth 2.0 Instructions</strong><br></p><p>To set up this plugin you need to setup OAuth 2.0 as all other authentication methods are officially deprecated. For this you need to register your LibraryLink site as a project with Google and get an OAuth client id and secret. There is no cost involved.</p><list><li>Log on to Google with any valid Google account (this does not need to be related to your YouTube account), then go to <a href=\"https://console.developers.google.com\" target=\"_blank\">https://console.developers.google.com</a></li><li>Create a new project (the name and ID don't matter, they are for your reference)</li><li>Expand 'APIs & auth', click APIs and then click the YouTube Data API</li><li>Click 'Enable API'</li><li>On the left hand side Select 'Credentials' and then 'Create new client ID'</li><li>Select 'Web Application' and click 'Configure consent screen'</li><li>Select an email address and enter a product name, then click 'Save'</li><li>Fill in the authorized javascript origins with your system base URL and the redirect URL with the callback URL specified at the top of this page and click 'Create Client ID'</li><li>Note down the client ID and secret then enter these details below</li><li>(Optional) Add a developer key. This is not currently essential but may become so. A developer key uniquely identifies a product that is submitting an API request. Please visit <a href=\"http://code.google.com/apis/youtube/dashboard/\" target=\"_blank\" >http://code.google.com/apis/youtube/dashboard/</a> to obtain a developer key.</li></list>";
$lang['winauth_info'] = "This plugin allows users to login to LibraryLink using Integrated Windows authentication.<br /><br />If you are unsure how to configure this please read the <a href='https://www.resourcespace.com/knowledge-base/user/plugin-winauth' target='_blank'>LibraryLink KnowledgeBase article.</a><br /><br /><strong>PLEASE NOTE:</strong> this plugin will not create new user accounts so they must be pre-created with the username matching that of their Windows username";
$lang['winauth_prefer_normal'] = "Prefer standard LibraryLink logins. If this is true then users will be redirected to the login page by default where there will be the option to use Windows Authentication";
$lang['emu_script_header'] = 'Enable script that will automatically update the EMu data whenever LibraryLink runs its scheduled task (cron_copy_hitcount.php)';
$lang['emu_search_criteria'] = 'Search criteria for syncing EMu with LibraryLink';
$lang['emu_rs_mappings_header'] = 'EMu - LibraryLink mapping rules';
$lang['emu_rs_field'] = 'LibraryLink field';
$lang['posixldapauth_resourcespace_configuration'] = 'LibraryLink Configuration';
# Language File for the LibraryLink Adobe Link Plugin
# Language File for the LibraryLink Falcon Link Plugin
$lang["falcon_link_tag_fields"]                     = "LibraryLink - Falcon tag fields. These will be concatenated and added to the Falcon template tags";
$lang['museumplus_RS_settings_header'] = 'LibraryLink settings';
$lang['museumplus_rs_mappings_header'] = 'MuseumPlus - LibraryLink mappings';
$lang['museumplus_rs_field'] = 'LibraryLink field';
$lang['ldaprsgroupmapping'] = "LDAP-LibraryLink Group Mapping";
$lang['rsgroup'] = "LibraryLink Group";
$lang['simpleldap_multiple_email_match_subject'] ="LibraryLink - conflicting email login attempt";
$lang['simpleldap_no_group_match_subject']="LibraryLink - new user with no group mapping";
$lang['simpleldap_no_group_match']="A new user has logged on but there is no LibraryLink group mapped to any directory group to which they belong.";
# Language File for the LibraryLink propose_changes plugin
$lang['cookies_notification_allow_using_site_on_no_feedback_label'] = "Allow users to continue using LibraryLink even if they didn't select one of the options?";
$lang['cookies_notification_cookies_use_error_msg'] = 'You have decided to not allow Cookies to be used by LibraryLink. We had to log you out as LibraryLink requires cookies in order to work properly.';
$lang['vimeo_publish_rs_field_mappings']         = 'LibraryLink - Vimeo field mappings';
$lang['vimeo_api_instructions_condition_1'] = 'You will need to register LibraryLink as an app with Vimeo and get an OAuth client ID and Secret';
$lang['vimeo_publish_no_vimeoAPI_files']          = 'LibraryLink seems to not be able to access Vimeo\'s PHP API files!';
$lang['vimeo_publish_not_configured']             = 'LibraryLink plugin "vimeo_publish" has not been configured. Please go to: ';
# Language File for the LibraryLink WordPress SSO Plugin
$lang['wordpress_sso_allow_standard_login']="Allow standard LibraryLink logins";
