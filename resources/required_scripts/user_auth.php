<?php
/*
+----------------------------------------------------------+
| Aploiki is distributed under the CC-GNU GPL license.     |
| You may NOT remove or modify any 'powered by' or         |
| copyright lines in the code.                             |
| http://creativecommons.org/licenses/GPL/2.0/             |
+----------------------------------------------------------+
| Made by Kyle Kirby                                       |
+----------------------------------------------------------+
*/
// this file checks to see if your logged in or not..
$_AEYNIAS['authentication']['user']['logged_in'] = false;
$_AEYNIAS['authentication']['admin']['logged_in'] = false;

if (isset($_SESSION['username']) && isset($_SESSION['password'])){
	if ($users_auth = $_AEYNIAS['class']['mysql']->quick_grab('users','username, password, admin, theme',array('username' => $_SESSION['username'],'password' => $_SESSION['password']))){
		$_AEYNIAS['authentication']['user']['logged_in'] = true;
		if ($users_auth['admin'] == 1)$_AEYNIAS['authentication']['admin']['logged_in'] = true;
		else $_AEYNIAS['authentication']['admin']['logged_in'] = false;
		$_AEYNIAS['authentication']['user']['username'] = $_SESSION['username'];
		$_AEYNIAS['authentication']['user']['password'] = $_SESSION['password'];
		$_AEYNIAS['authentication']['user']['theme'] = isset($_POST['theme']) ? $_POST['theme'] : $users_auth['theme'];
	}
	else error($_AEYNIAS['class']['mysql']->error);
}
?>