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
class post_form{
	function post_form(){}
	
	function set_keys($keys){
		$this->md5 = $keys[0];
		$this->required = $keys[1];
	}
	
	function check_keys(){
		if (!isset($this->md5)){
			error('Keys not set, please notify the administrator of this error.');
			return false;
			exit;
		}
		$md5_check = '';
		foreach($_POST as $key=>$value)$md5_check .= $key;
		if (md5($md5_check) == $this->md5){
			return true;
		}
		else return false;
	}
	
	function check_form($check_keys = 1){
		if ($this->check_keys() || $check_keys == 0){
			foreach($this->required as $key){
				if ($_POST[$key[0]] == ''){
					error($key[1]);
				}
			}
			if (error() != 0)return false;
			else return true;
		}
		else {
			error('Something went horribly wrong..');
		}
	}
	
}

class make_form{
	function make_form($action,$method = "post"){
		$this->action = $action;
		$this->method = $method;
		$this->required_array = array();
		$this->md5 = '';
		if (strtolower($method) == "post"){
			$this->form_html = '<form method="POST" action="' . $action . '">' . "\n";
		}
	}
	
	function clear_post(){
		$this->cleat_post = 1;
	}
	
	function insert_input($text,$name,$default = null,$required=0,$custom_error = null){
		$default = (isset($_POST[$name])) ? $_POST[$name] : $default;
		$this->form[] = array(
			'type' => 'text',
			'text' => $text,
			'name' => $name,
			'value' => $default);
		$this->md5 .= $name;
		$error_msg = ($custom_error != null) ? $custom_error : $text . ' is required.';
		($required == 1 || $required == TRUE) ? $this->required_array[] = array($name,$error_msg) : null;
		
		
		//$this->form_html .= '<b>'.$text.'</b>: <input type="text" name="' . $name . '" value="' . $default_value . '">' . "<br/>\n";
	}
	
	
	function insert_submit($value = 'Submit',$name = 'submit'){
		$this->form[] = array (
			'type' => 'submit',
			'name' => $name,
			'value' => $value);
		$this->md5 .= $name;
		
	}
	
	function insert_hidden($value,$name,$required=1,$error_msg = null){
		$this->form[] = array(
			'type'=>'hidden',
			'name'=>$name,
			'value'=>$value);
		($required == 1) ? $this->required_array[] = array($name,$error_msg) : null;
	}
	
	function insert_anything($html){
		$this->form[] = array(
			'type'=>'html',
			'html'=>$html);
	}
	
	function insert_select($text, $array,$name,$default = '',$required=1,$custom_error = ''){
		if (isset($_POST[$name]))$default = $_POST[$name];
		
		$options = array();
		foreach ($array as $value=>$name2){
			if ($default == $value)$selected = '1';
			else $selected = '0';
			$options[] = array(
				'value'=>$value,
				'selected'=>$selected,
				'name' => $name2);
		}
		
		$this->form[] = array(
			'type' => 'select',
			'name' => $name,
			'text' => $text,
			'options' => $options);
		$this->md5 .= $name;
		$error_msg = ($custom_error != null) ? $custom_error : $text . ' is required.';
		($required == 1 || $required == TRUE) ? $this->required_array[] = array($name,$error_msg) : null;
		//$this->form_html .= "<b>$text:</b> " . '<select name="' . $name . '">' . "\n";
		//$this->form_html .= "\t" . '<option value="'.$value.'"'. $selected . '>' . $name2 . '</option>' . "\n";
		//$this->form_html .= "</select><br>\n";
	}
	
	function insert_password($text,$name,$required=0,$custom_error = null){
		$this->form[] = array(
			'type' => 'password',
			'text' => $text,
			'name' => $name);
		
		$this->md5 .= $name;
		$error_msg = ($custom_error != null) ? $custom_error : $text . ' is required.';
		($required == 1 || $required == TRUE) ? $this->required_array[] = array($name,$error_msg) : null;
		
		//$this->form_html .= "<b>$text:</b> " . '<input type="password" name="'.$name.'">' . "<br/>\n";
	}

	function insert_textarea($text,$name,$rows,$cols,$default = null,$required=0,$custom_error = null){
		$default = (isset($_POST[$name])) ? $_POST[$name] : $default;
		$this->form[] = array(
			'type'=>'textarea',
			'text' => $text,
			'name' => $name,
			'rows' => $rows,
			'cols' => $cols,
			'default' => $default);
		$this->md5 .= $name;
		$error_msg = ($custom_error != null) ? $custom_error : $text . ' is required.';
		($required == 1 || $required == TRUE) ? $this->required_array[] = array($name,$error_msg) : null;
		//$this->form_html .= "<b>$text:</b> " . '<textarea type="password" name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'"></textarea>' . "<br/>\n";
	}
	
	function get_keys(){
		return array(md5($this->md5),$this->required_array);
	}
	
	function return_form(){
		foreach($this->form as $form_item){
			switch($form_item['type']){
				case('text'):
					$this->form_html .= "<label>$form_item[text]</label> " . '<input type="text" value="'.$form_item['value'].'" name="'.$form_item['name'].'"/><br/>' . "\n";
				break;
				case('password'):
					$this->form_html .= "<label>$form_item[text]</label> " . '<input type="password" name="'.$form_item['name'].'"/>' . "<br/>\n";
				break;
				case('hidden'):
					$this->form_html .= '<input type="hidden" value="'.$form_item['value'].'" name="'.$form_item['name'].'"/>'."\n";
				break;
				case('html'):
					$this->form_html .= $form_item['html'];
				break;
				case('select'):
					$this->form_html .= "<label>$form_item[text]</label> " . '<select name="' . $form_item['name'] . '"/>' . "\n";
					foreach ($form_item['options'] as $option){
						$selected = ($option['selected'] == 1) ? ' selected' : '';
						$this->form_html .= "\t" . '<option value="'.$option['value'].'"'. $selected . '>' . $option['name'] . '</option>' . "\n";
					}
					$this->form_html .= "</select><br/>\n";
				break;
				case('textarea'):
					$this->form_html .= "<label>$form_item[text]</label><br> " . '<textarea name="'.$form_item['name'].'" rows="'.$form_item['rows'].'" cols="'.$form_item['cols'].'">'.$form_item['default'].'</textarea>' . "<br/>\n";
				break;
				case('submit'):
					$this->form_html .= '<input type="submit" name="'.$form_item['name'].'" value="'.$form_item['value'].'" class="submit_button"/>' . "<br/>\n";
				break;
			}
		}
		$this->form_html .= '</form>';
		
		return $this->form_html;
		
	}
}

?>