<?php
/*
 * API v1 : Bindings to built in functions
 *
 * Library Link API functions
 */

function api_librarylink_test($resource)
    {
        debug("LibraryLink Test - $resource");
        return "LibraryLink Test - $resource";
    }


function api_librarylink_add_links($resource, $links_csv, $add_keywords = true)
    {
    return "Not yet implemented";
    }

function api_librarylink_delete_links($resource, $links_csv = "", $delete_keywords = true)
    {
    return "Not yet implemented";
    }