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
class menu {
    function menu(){
        global $_AEYNIAS;
        $this->menu_array = isset($_AEYNIAS['config']['menu']['items']) ? $_AEYNIAS['config']['menu']['items'] : array();
        // just a default template.
        $this->template = array(
    			'overall'=>'<ol id="menu">%items</ol>',
    			'per'=>'<li class="menu_item"><A href="%location">%title</a></li>',
    			'per_active' => '<li class="menu_item active">%title</li>'
    		);
    }
    
    function add_item($array){
        if (is_array($array[0]))$this->menu_array = array_merge($this->menu_array,$array);
        else if (is_array($array))$this->menu_array[] = $array;
    }
    
    function remove_item($supplied_array,$strict = false){
        if (is_array($this->menu_array)){
            if (is_array($supplied_array[0])){
                foreach($supplied_array as $the_array_to_use_for_everything)$this->remove_item($the_array_to_use_for_everything,$strict);
            }
            else if (is_array($supplied_array)){
                foreach($this->menu_array as $menu_item){
                    $match = 0;
                    if ($strict == false){
                        foreach($supplied_array as $supplied_array_key=>$supplied_array_value){
                            if (isset($menu_item[$supplied_array_key]) && $menu_item[$supplied_array_key] == $supplied_array_value){
                                $match = 1;
                                break;
                            }
                        }
                    }
                    else {
                        if ($menu_item == $supplied_array)$match = 1;
                        else $match = 0;
                    }
                    if ($match == 0)$output[] = $menu_item;
                }
                $this->menu_array = $output;
            }
        }
    }

    
    function produce_menu(){
        global $_AEYNIAS;
        
    	$items = $this->menu_array;
    	$template = $this->template;
    	$menu_items = '';

    	foreach ($items as $item){
    	    $skip = false;
    	    $item['title'] = !isset($item['title']) ? $item['page'] : $item['title'];
    	    $item['strict'] = !isset($item['strict']) ? false : $item['strict'];
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

    				$is_viewing = ((($_AEYNIAS['parameters']['page'] == $item['page'] && $item['parameters'] == $_AEYNIAS['parameters']['extras']) && $item['strict'] == true) || ($_AEYNIAS['parameters']['page'] == $item['page'] && ($item['strict'] == false || !isset($item['strict'])))) ? true : false;

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
    
}
$_AEYNIAS['class']['menu'] = new menu;

?>