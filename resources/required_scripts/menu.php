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
function generate_menu($menu_array){
	global $_AEYNIAS;
	$items = $menu_array['items'];
	$template = $menu_array['template'];
	$menu_items = '';

	foreach ($items as $item){
	    $skip = false;
	    $item['title'] = !isset($item['title']) ? $item['page'] : $item['title'];
		if (
			(isset($item['logged_in']) && ($item['logged_in'] == 1 && $_AEYNIAS['authentication']['user']['logged_in'] === true))
		||	(isset($item['logged_in']) && ($item['logged_in'] == 0 && $_AEYNIAS['authentication']['user']['logged_in'] === false))
		||	(isset($item['logged_in']) && ($item['logged_in'] == 2 && $_AEYNIAS['authentication']['admin']['logged_in'] === true))
		||  (!isset($item['logged_in']))){
			if (isset($item['page'])){
				if ($item['page'] == 'main')$url = $_AEYNIAS['config']['doc_url'] . '/';
				else {
				    if (isset($item['logged_in']) && $item['logged_in'] == 2)$url = $_AEYNIAS['config']['doc_url'] . '/admin/' . $item['page'] . '/';
				    else $url = $_AEYNIAS['config']['doc_url'] . '/' . $item['page'] . '/';
				}
				if (isset($item['parameters'])){
				    foreach($item['parameters'] as $param)$url .= $param .'/';
				}
				else $item['parameters'] = array();
			
				$is_viewing = ($_AEYNIAS['parameters']['page'] == $item['page'] && $item['parameters'] == $_AEYNIAS['parameters']['extras']) ? true : false;
			
				if ($is_viewing && 
				    (isset($item['logged_in']) && $item['logged_in'] != '2' && 
				        $_AEYNIAS['parameters']['admin'] === false))
				            $template_item = $template['per_active'];

				else if ($is_viewing && 
				    (isset($item['logged_in']) && $item['logged_in'] == '2' && 
				        $_AEYNIAS['parameters']['admin'] === true))
				            $template_item = $template['per_active'];
			            
				else if ($is_viewing)$template_item = $template['per_active'];
				else $template_item = $template['per'];
			}
			else if (isset($item['action'])){
				$url = $_AEYNIAS['config']['doc_url'] . '/action/' . $item['action'] .'/';
				if (isset($item['input']))$url .= $item['input'] . '/';
				$template_item = $template['per'];
			}
			else $skip = true;
			$menu_items .= $skip == false ? str_replace(array('%location','%title'),array($url,$item['title']),$template_item) : '';
    	}
	}
	
	return str_replace('%items',$menu_items,$template['overall']);
}

$_AEYNIAS['menu'] = generate_menu($_AEYNIAS['config']['menu']);
?>