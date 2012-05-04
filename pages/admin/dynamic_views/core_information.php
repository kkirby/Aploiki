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
$page_css = 'pre {word-wrap: break-word;}';

$params = $_AEYNIAS['parameters']['extras'];

$_AEYNIAS['class']['menu']->add_item(array('title'=>'Show Template','page'=>'core_information','parameters'=>array('show_template'),'logged_in'=>2,'strict'=>true));

if (isset($params['0']) && $params['0'] == 'show_template'){
    $base = htmlspecialchars($this->_class['base_html']['text']['raw']);
    $page_text = '<pre>'.$base.'</pre>';
    
}
else {
    $_AEYNIAS['config']['menu']['items'][] = array('title'=>'Template','module'=>'debug','logged_in'=>'2','parameters'=>array('show_template','one_two'));
    $page_text = '<pre>'. htmlspecialchars(print_r($_AEYNIAS,true)) . '</pre>';
}
?>