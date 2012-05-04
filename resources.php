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
///////////////////

function error($text = null,$field = null){
	global $errors;
	if ($text == null){
		if($errors != "")return true;
		else return false;
	}
	else {
		if ($field != null){
			if (empty($field))$errors[] = $text;
		}
		else $errors[] = $text;
		return true;
	}
}

function praise($text = null){
	global $praises;
	if ($text == null){
		if ($praises != '')return true;
		else return false;
	}
	else $praises[] = $text;
}



///////////////////

function arraytolower($array){
    foreach($array as $key=>$value)$array_done[strtolower($key)] = strtolower($value);
    return $array_done;
}

/// get classes ///
if (isset($_AEYNIAS['config']['use_mysql']) && $_AEYNIAS['config']['use_mysql'] == true){ 
    require($_AEYNIAS['config']['doc_root'] . '/resources/classes/mysql_class.php');
    require($_AEYNIAS['config']['doc_root'] . '/resources/classes/dataModel.class.php');
    $_AEYNIAS['class']['mysql'] = new _mysql(); // set up mysql, Since it should be loaded.. no?
    require($_AEYNIAS['config']['doc_root'] . '/resources/required_scripts/user_auth.php');
}

require($_AEYNIAS['config']['doc_root'] . '/resources/classes/menu_class.php');
//require($_AEYNIAS['config']['doc_root'] . '/resources/classes/form_class.php');

require($_AEYNIAS['config']['doc_root'] . '/resources/classes/new_form_class.php');

require($_AEYNIAS['config']['doc_root'] . '/resources/classes/time_class.php');

require($_AEYNIAS['config']['doc_root'] . '/resources/classes/theme_class.php');


if (!is_file($_AEYNIAS['config']['doc_root'] . '/.htaccess'))error('You need to rename the file "rename.htaccess" to ".htaccess"!!');
require($_AEYNIAS['config']['doc_root'] . '/resources/classes/template_class.php');

?>