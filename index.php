<?php
ERROR_REPORTING(E_ALL);
/*
+----------------------------------------------------------+
| Aploiki is distributed under the CC-GNU GPL license.     |
| You may NOT remove or modify any 'powered by' or         |
| copyright lines in the code.                             |
| http://creativecommons.org/licenses/GPL/2.0/             |
+----------------------------------------------------------+
| Made by Kyle Kirby                                       |
+----------------------------------------------------------+


Version: R1.4

*/
session_start();                                                                            // Start up those sessions!
require('./config.php');
$_AEYNIAS['parameters']['full_url'] = $_AEYNIAS['config']['doc_url'];                       // Set up the full URL to this page by putting the document url.

if (isset($_GET['action'])){                                                                // an action is being viewed..
	$_AEYNIAS['parameters']['action'] = true;                                               // Inform the script of it..
	$_AEYNIAS['parameters']['page'] = $_GET['action'];                                      // Set the page..
	if (strpos($_AEYNIAS['parameters']['action'],'..') === true)die('Naughty, naughty.');   // Catch people trying to view files not in the actions directory..
	$_AEYNIAS['parameters']['full_url'] .= '/action/';                                       // Add /action/ to the full url.
}
else if(isset($_GET['module'])){
    $_AEYNIAS['parameters']['module'] = true;
    $_AEYNIAS['parameters']['page'] = $_GET['module'];
}
else {
	$_AEYNIAS['parameters']['page'] = (isset($_GET['page'])) ? $_GET['page'] : 'main';      // If the page isn't set, revert back to main..
	if (strpos($_AEYNIAS['parameters']['page'],'..') === true)die('Naughty, naughty.');     // Catch people trying to view files not in the static_views directory..
	
	$_AEYNIAS['parameters']['admin'] = (isset($_GET['admin'])) ? true : false;              // If its an admin page, tell the script so..
	$_AEYNIAS['parameters']['full_url'] .= (isset($_GET['admin'])) ? '/admin/' : '/';       // Add /admin or / to the full URL accordingly..
	$_AEYNIAS['parameters']['action'] = false;                                              // Tell the script it is NOT an action.
}
$_AEYNIAS['parameters']['full_url'] .= $_AEYNIAS['parameters']['page'] . '/';               // Add the page beinging viewed to the Full URL.


$_AEYNIAS['parameters']['extras'] = array();                                                // Initalize the Extras array..
if (isset($_GET['parameters'])){
        $params = explode('/', trim($_GET['parameters'], '/'));
        foreach ($params as $param){
            $_AEYNIAS['parameters']['extras'][] = $param;
            $_AEYNIAS['parameters']['full_url'] .= $param . '/';
        }
}

$errors = '';                                                                               // Errors set to none.
$praises = '';                                                                              // Praises set to none.

require($_AEYNIAS['config']['doc_root'] . '/resources.php');                                // Include the resources..
?>