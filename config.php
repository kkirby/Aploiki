<?php
$enviroment = 'server';	// Set which server to use below.


// Switched used for multiple servers.
switch($enviroment){
	case('server'):
		// basic information for AEYNIAS to work properly
		$_AEYNIAS['config']['mysql']['server'] = 'localhost';
		$_AEYNIAS['config']['mysql']['username'] = 'root';
		$_AEYNIAS['config']['mysql']['password'] = 'root';
		$_AEYNIAS['config']['mysql']['database'] = 'aploiki';
		$_AEYNIAS['config']['doc_root'] = realpath('.');		// No trailing slashes..
		$_AEYNIAS['config']['doc_url'] ='http://localhost/';						// No trailing slashes.
		$_AEYNIAS['config']['timezone_offset'] = round(@date('P',time()));		// Timezone offset of the area.. You can manually speicify this.
		$_AEYNIAS['config']['default_theme'] = 'modern_blue';
		$_AEYNIAS['config']['use_mysql'] = true;
		$_AEYNIAS['config']['error_messages']['404'] = "The page you were looking for was not found.";
	break;
}

/* defaults for template */
		
$_AEYNIAS['config']['menu']['items'] = array(
		array(
			'page'=>'main',                 // main is the default module when none is presented in the URL (views/*my_module*)
			'title'=>'Home'                 // The title for the menu item.
										    // If array item 'logged_in' is not listed, it is declared as always showing.
		),
		array(
				'page'=>'user_options',	    // Directs to the action Logout, see inside the "Actions" folder.
				'title' => 'Options',	    // The title for the menu item.
				'logged_in' => '1'		    // You must be logged in for this menu item to show up.
		),
		array(
				'page'=>'core_information',	        // Directs to the admin page debug, see below at logged_in => 2
				'title' => 'Core Information',	    // The title for the menu item.
				'logged_in' => '2',		            // You must be an admin to see this.
				'strict' => true
		),
		array(
				'action'=>'logout',	    	// Directs to the action Logout, see inside the "Actions" folder.
				'title' => 'Logout',    	// The title for the menu item.
				'logged_in' => '1'	    	// You must be logged in for this menu item to show up.
		),
		array(
				'page'=>'signup',	    	// Direct to the module "signup" (//signup/)
				'title' => 'Sign Up',   	// The Title for the menu item
				'logged_in' => '0'	    	// You have to be logged out for this item to show.
		),
		array(
				'page'=>'login',	    	// Direct to the "login" module (//login/)
				'title' => 'Login',	    	// The title for the menu item
				'logged_in' => '0'	    	// You have to be logged out for this item to show.
		)
	);


?>