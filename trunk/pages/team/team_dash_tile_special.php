<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
if(!((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))){exit($lang["error-permissiondenied"]);}
include "../../include/dash_functions.php";

include "../../include/header.php";
?>
<div class="BasicsBox"> 
<h1><?php echo $lang["specialdashtiles"];render_help_link('user/manage-dash-tile');?></h1>
<p></p>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_home.php" onClick="return CentralSpaceLoad(this,true);">
		<?php echo LINK_CARET_BACK ?><?php echo $lang["backtoteamhome"]?>
	</a>
</p>
<p>
    <a href="<?php echo $baseurl_short?>pages/team/team_dash_tile.php" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK; ?><?php echo $lang['view_tiles']; ?></a>
</p>
<p>
    <a href="<?php echo $baseurl_short?>pages/team/team_dash_admin.php" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK; ?><?php echo $lang['dasheditmodifytiles']; ?></a>
</p>

<h2><?php echo $lang["createnewdashtile"];?></h2>
<p></p>
<ul>
	<li>
		<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&tltype=ftxt&modifylink=true&freetext=Helpful%20tips%20here&nostyleoptions=true&tile_audience=true&link=http://resourcespace.org/knowledge-base/&title=Knowledge%20Base";?>">
			<?php echo $lang["createdashtilefreetext"];?>
		</a>
	</li>
	<li>
		<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&tltype=ftxt&freetext=true&title=Upload&nostyleoptions=true&tile_audience=true&link=pages/edit.php%3Fref=-[userref]%26uploader=plupload";?>">
			<?php echo $lang["createdashtileuserupload"];?>
		</a>
	</li>
</ul>
<h2><?php echo $lang["alluserprebuiltdashtiles"];?></h2>
<p></p>
<ul>
	<li>
		<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=pend&freetext=userpendingsubmission&tile_audience=true&link=/pages/search.php?search=%26archive=-2";?>">
			<?php echo $lang["createdashtilependingsubmission"];?>
		</a>
	</li>
	<li>
		<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=pend&freetext=userpending&tile_audience=true&link=/pages/search.php?search=%26archive=-1";?>">
			<?php echo $lang["createdashtilependingreview"];?>
		</a>
	</li>
	<?php 
	/* Old Configuration tiles */
	if($enable_themes && !$home_themeheaders)
		{ ?>
		<li>
			<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=thmsl&title=themeselector&tile_audience=true&link=pages/themes.php&url=pages/ajax/dash_tile.php%3Ftltype=conf%26tlstyle=thmsl";?>">
				<?php echo $lang["createdashtilethemeselector"];?>
			</a>
		</li>
		<?php
		}
	if($enable_themes && !$home_themes)
		{ ?>
		<li>
			<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=theme&title=themes&tile_audience=true&link=pages/themes.php&url=pages/ajax/dash_tile.php%3Ftltype=conf%26tlstyle=theme";?>">
				<?php echo $lang["createdashtilethemes"];?>
			</a>
		</li>
		<?php
		}
	if(!$home_mycollections)
		{ ?>
		<li>
			<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=mycol&title=mycollections&tile_audience=true&link=pages/collection_manage.php&url=pages/ajax/dash_tile.php%3Ftltype=conf%26tlstyle=mycol";?>">
				<?php echo $lang["createdashtilemycollections"];?>
			</a>
		</li>
		<?php
		}
	if(!$home_mycontributions)
		{ ?>
		<li>
			<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=mycnt&title=mycontributions&tile_audience=true&link=pages/contribute.php&url=pages/ajax/dash_tile.php%3Ftltype=conf%26tlstyle=mycnt";?>">
				<?php echo $lang["createdashtilemycontributions"];?>
			</a>
		</li>
		<?php
		}
	if(!$home_helpadvice)
		{ ?>
		<li>
			<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&submitdashtile=true&tltype=conf&tlstyle=hlpad&title=helpandadvice&tile_audience=true&link=pages/help.php&url=pages/ajax/dash_tile.php%3Ftltype=conf%26tlstyle=hlpad";?>">
				<?php echo $lang["createdashtilehelpandadvice"];?>
			</a>
		</li>
		<?php
		}
	?>

</div>
<?php
include "../../include/footer.php";
?>
