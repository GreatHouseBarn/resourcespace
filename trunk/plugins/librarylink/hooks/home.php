<?php
include_once(__DIR__."/../../../librarylink/api/include/api_functions.php");

function HookLibrarylinkHomeHomereplacewelcome()
    {
        global $welcome_text_picturepanel,$no_welcometext,$home_dash,$productversion,$lang;
        ?>
            <div class="BasicsBox <?php echo $home_dash ? 'dashtext':''; ?>" id="HomeSiteText">
                <div id="HomeSiteTextInner">
                <h1><?php 
            # Include version number, but only when this isn't an SVN checkout. Also, show just the first two digits.
            echo str_replace("[ver]",str_replace("SVN","",substr($productversion,0,strrpos($productversion,"."))),text("llwelcometitle")) ?></h1>
                <p><?php printf(text("llwelcometext"), @file_get_contents(__DIR__."/../../../client.txt")); ?></p>
                </div>
            </div>
            <?php 
        return false;
    }