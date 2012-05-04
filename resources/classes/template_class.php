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
class _aeynias_template{
    function _aeynias_template($custom_text = false){
        global $_AEYNIAS;
        if (isset($_AEYNIAS['parameters']['action']) && $_AEYNIAS['parameters']['action'] === true){
            // its an action.
            $action_url = $_AEYNIAS['config']['doc_root'] . '/actions/' . $_AEYNIAS['parameters']['page'] . '.php';
            if(is_readable($action_url)){
                include($action_url);
                exit;
            }
            else $this->_error('Action not found.');
        }
        else {
            if ($this->_load_base_template()){
                if (stristr($this->_class['page']['text']['raw'],'!dont_use_template') !== false || (isset($this->_class['page']['use_template']) && $this->_class['page']['use_template'] === false))
                    echo $this->_class['page']['text']['parsed'];
                else
                    echo $this->_class['base_html']['text']['parsed'];
            }
        }
    }
    function use_template($value = false){
        $this->_class['page']['use_template'] = false;
    }
    function _load_module(){
        global $_AEYNIAS;
        include($_AEYNIAS['config']['doc_root'] . '/resources/classes/module_class.php');
        
        if (is_readable($_AEYNIAS['config']['doc_root'] . '/modules/' . $this->_class['module'] . '/main.php'))
            include($_AEYNIAS['config']['doc_root'] . '/modules/' . $this->_class['module'] .'/main.php');
        else if (is_readable($_AEYNIAS['config']['doc_root'] . '/modules/' . $this->_class['module'] . '/' . $this->_class['module'] . '.php'))
            include($_AEYNIAS['config']['doc_root'] . '/modules/' . $this->_class['module'] . '/' . $this->_class['module'] . '.php');
        
        $module_class = new module_addon;
        $module = $module_class->show_text();
        $this->_class['page']['title'] = $module[0];
        $this->_class['page']['text']['override'] = $module[1];
        $this->_class['page']['css'] = $module[2];
        $this->_load_base_template();
        echo $this->_class['base_html']['text']['parsed'];
    }
    function _grab_matching_module(){
        global $_AEYNIAS;
        if (is_readable($_AEYNIAS['config']['doc_root'] . '/modules/' . $_AEYNIAS['parameters']['page'] . '/main.php'))return $_AEYNIAS['parameters']['page'];
        else if (is_readable($_AEYNIAS['config']['doc_root'] . '/modules/' . $_AEYNIAS['parameters']['page'] . '/' . $_AEYNIAS['parameters']['page'] . '.php'))return $_AEYNIAS['parameters']['page'];
        else return false;
    }
    
    private function _load_base_template(){
       global $_AEYNIAS;
       // grab the base.phtml file
       $theme_info = $_AEYNIAS['class']['theme']->grab_theme();
       if ($theme_info['status'] && is_readable($_AEYNIAS['config']['doc_root'] . '/themes/' . $theme_info['folder'] . '/base.phtml')){
           $folder_context = $_AEYNIAS['config']['doc_root'] . '/themes/' . $theme_info['folder'];
           $this->_class['base_html']['text']['raw'] = @file_get_contents($folder_context . '/base.phtml');
       }
       else {
           $folder_context = $_AEYNIAS['config']['doc_root'] . '/pages';
           $this->_class['base_html']['text']['raw'] = @file_get_contents($folder_context . '/base.phtml');
       }
       if ($this->_class['base_html']['text']['raw'] !== false){
           if(stristr($this->_class['base_html']['text']['raw'],'!import_dynamic') !== false){
               //we need to laod the dynamic base.phtml file.
                if ($this->_load_page_text() === false)return false;
                else {
                    $this->_grab_static_page_information();
                    $page_css = $this->_class['page']['css'];
                    $page_title = $this->_class['page']['title'];
                    $page_text = $this->_class['page']['text']['parsed'];
                    $menu = $_AEYNIAS['class']['menu']->produce_menu();
                    if (!is_readable($folder_context . '/base.php')){
                        if ($_AEYNIAS['authentication']['admin']['logged_in'] == true)error('No dynamic base file found. Reverting to static..');
                        $this->_replace_information();
                        $this->_class['base_html']['text']['parsed'] = str_replace('!import_dynamic','',$this->_class['base_html']['text']['parsed']);
                        return true;
                    }
                    else {
                        include($folder_context . '/base.php');
                        $this->_class['base_html']['text']['parsed'] = $base_template;
                        return true;
                    }
                }
           }
           else {
               // its just the simple base.phtml file, nothing special here.
               if ($this->_load_page_text() === false)return false;
               else {
                   $this->_grab_static_page_information();
                   $this->_replace_information();
                   return true;
               }
           }
       }
       else $this->_critial_error('No base.phtml file found!'); // Its a critial error, becasue we have no template to base anything off of, espically the error.
    }
    private function _critial_error($message){
        // yeah its just a proxy, nice right? Probably not the best of code, but good for future development.
        die($message);
    }
    private function _error($message){
        $this->_class['page']['title'] = 'Error';
        $this->_class['page']['text']['override'] = '';
        $this->_class['page']['css'] = '';
        error($message);
        $this->_load_base_template();
        echo $this->_class['base_html']['text']['parsed'];
    }
    
    private function _process_menu_template(){
        global $_AEYNIAS;
        if (preg_match('!<menu>(.*)</menu>!s',$this->_class['base_html']['text']['parsed'],$menu_matches)){
            $menu_match = $menu_matches[1];
            //$menu_template_array = $_AEYNIAS['config']['menu']['template'];
            if(preg_match('!<per>(.*)</per>!s',$menu_match,$per_matches))$_AEYNIAS['class']['menu']->template['per'] = trim($per_matches[1]);
            if(preg_match('!<per-active>(.*)</per-active>!s',$menu_match,$per_active_matches))$_AEYNIAS['class']['menu']->template['per_active'] = trim($per_active_matches[1]);
            if(preg_match('!<overall>(.*)</overall>!s',$menu_match,$overall_matches))$_AEYNIAS['class']['menu']->template['overall'] = trim($overall_matches[1]);
            $this->_class['base_html']['text']['parsed'] = preg_replace('!<menu>.*</menu>!s','',$this->_class['base_html']['text']['parsed']);
        }
    }
    
    private function _replace_information(){
        global $_AEYNIAS;
        if (!isset($this->_class['page']['use_template']) || $this->_class['page']['use_template'] === true){
            $this->_class['base_html']['text']['parsed'] = $this->_class['base_html']['text']['raw'];
            $this->_process_menu_template();
    		$this->_class['base_html']['text']['parsed']  = preg_replace('/@menu/',$_AEYNIAS['class']['menu']->produce_menu(),$this->_class['base_html']['text']['parsed']);
    		$this->_class['base_html']['text']['parsed']  = preg_replace('/@page_title/',$this->_class['page']['title'],$this->_class['base_html']['text']['parsed']);	// replace @page_title in the base_template from the view template.
    		$this->_class['base_html']['text']['parsed']  = $this->replace_error($this->_class['base_html']['text']['parsed']);
    		$this->_class['base_html']['text']['parsed']  = $this->replace_praise($this->_class['base_html']['text']['parsed']);
    		$this->_class['base_html']['text']['parsed']  = $this->parse_functions($this->_class['base_html']['text']['parsed']);
    		$this->_class['base_html']['text']['parsed']  = preg_replace('/@text/',$this->_class['page']['text']['parsed'],$this->_class['base_html']['text']['parsed']);	// put the view template inside the base template
    		if ($this->_class['page']['css'] == '' && preg_match('!<style.*>\s*@css\s*</style>!',$this->_class['base_html']['text']['parsed']))$this->_class['base_html']['text']['parsed'] = preg_replace('!<style.*>\s*@css\s*</style>!','',$this->_class['base_html']['text']['parsed']);
    		else if($this->_class['page']['css'] == '' && preg_match('!@css!',$this->_class['base_html']['text']['parsed']))$this->_class['base_html']['text']['parsed'] = preg_replace('!@css!','',$this->_class['base_html']['text']['parsed']);
    		else if(preg_match('!<style.*>\s*@css\s*</style>!',$this->_class['base_html']['text']['parsed']))$this->_class['base_html']['text']['parsed'] = preg_replace('/@css/',$this->_class['page']['css'],$this->_class['base_html']['text']['parsed']);
    		else $this->_class['base_html']['text']['parsed'] = preg_replace('/@css/','<style type="text/css">' . $this->_class['page']['css'] . '</style>',$this->_class['base_html']['text']['parsed']);	// put the view template inside the base template
            $theme_info = $_AEYNIAS['class']['theme']->grab_theme();
            $this->_class['base_html']['text']['parsed']  = preg_replace('/@theme_html/',$theme_info['html'],$this->_class['base_html']['text']['parsed']);
        }
    }
    private function _load_page_text(){
        global $_AEYNIAS;
        if (isset($this->_class['page']['text']['override'])){
            $this->_class['page']['text']['raw'] = $this->_class['page']['text']['override'];
            $this->_class['page']['text']['parsed'] = $this->_class['page']['text']['override'];
        }
        else{
            $static_document_url = $_AEYNIAS['parameters']['admin'] == true ? $_AEYNIAS['config']['doc_root'] . '/pages/admin/static_views/' . $_AEYNIAS['parameters']['page'] . '.phtml' : $_AEYNIAS['config']['doc_root'] . '/pages/static_views/' . $_AEYNIAS['parameters']['page'] . '.phtml';
            $dynamic_document_url = $_AEYNIAS['parameters']['admin'] == true ? $_AEYNIAS['config']['doc_root'] . '/pages/admin/dynamic_views/' . $_AEYNIAS['parameters']['page'] . '.php' : $_AEYNIAS['config']['doc_root'] . '/pages/dynamic_views/' . $_AEYNIAS['parameters']['page'] . '.php';
            $this->_class['page']['text']['raw'] = @file_get_contents($static_document_url);
            $this->_class['page']['text']['parsed'] = '';
            $this->_class['page']['title'] = $_AEYNIAS['parameters']['page']; // defaults to the page if no title is given in the future of this class.
            $this->_class['page']['css'] = '';      // Defulats to none, if nothing is provided later on...
            if ($this->_class['page']['text']['raw'] !== false){
                if ($this->_logged_in()){       // Woah, forgot what the does. It looks like it would return true/false if your logged in or not.. But I forgot that it actualyl sends you to the login page if your not logged in.. so techinially it always returns true.. or not at all..
                    if (stristr($this->_class['page']['text']['raw'],'!use_module_equivalent') !== false && is_readable($_AEYNIAS['config']['doc_root'] . '/modules/' . $_AEYNIAS['parameters']['page'] . '/main.php')){
                          $this->_class['module'] = $_AEYNIAS['parameters']['page'];
                          $this->_load_module();
                          return false;
                    }
                    else {
                        if (stristr($this->_class['page']['text']['raw'],'!dont_use_template') !== false)$this->_class['page']['use_template'] = false;
                        if(stristr($this->_class['page']['text']['raw'],'!import_dynamic') !== false){
                            // its a dynamic page
                            if (is_readable($dynamic_document_url)){
                                include($dynamic_document_url);
                                if (isset($page_title))$this->_class['page']['title'] = $page_title;
                                if(isset($page_css))$this->_class['page']['css'] = $page_css;
                                if(isset($page_text))$this->_class['page']['text']['parsed'] = $page_text;
                            }
                            else {
                                error('No dynamic document found, reverting to static.');
                                $this->_strip_data();
                            }
                        }
                        else {
                            // its not a dyniamc page.
                            $this->_strip_data();
                        }
                    }
                }
                else $this->_error('Its a sign of the apocalypse!!!! Ahhhhhh!!!!');      // Coders have to have some fun... Right?
            }
            else {
                if ($this->_class['module'] = $this->_grab_matching_module()){
                    $this->_load_module();
                    return false;
                }
                else {
                    $this->_error(isset($_AEYNIAS['config']['error_messages']['404']) ? $_AEYNIAS['config']['error_messages']['404'] : 'The page you were looking for was not found on this server.');   // Plain error, we can use the template to display this error.
                    return false;
                }
            }
        }
    }
    private function _grab_static_page_information(){
        if(preg_match('/@page_title = "(.*)"/',$this->_class['page']['text']['raw'],$page_title))$this->_class['page']['title'] = $page_title[1];	        // Define page title, if its there
		if(preg_match('/(?<=@css\s=\s\")(?:{.+}|[^\"])+/',$this->_class['page']['text']['raw'],$page_css))$this->_class['page']['css'] = $this->_class['page']['css'] == '' ? $page_css[0] : $this->_class['page']['css'] . "\n" . $page_css[0];	// Define CSS if its there, thanks Bokeh for the snippit.
    }
    private function _logged_in(){
        global $_AEYNIAS;
        if((stristr($this->_class['page']['text']['raw'],'!logged_in') !== false && $_AEYNIAS['authentication']['user']['logged_in'] === false) || ($_AEYNIAS['parameters']['admin'] === true && $_AEYNIAS['authentication']['admin']['logged_in'] === false)){
            // either the text !llogged_in is there and the user is not logged in, OR its an admin page and the user is not an admin, or isn't even logged in..
			
			$minutes = 3;   // Amount of minutes until the session expires.. Checked within Login.php.. I suppose I could just use cookies, yeah?...
			$minutes = $minutes * 60;
			$_SESSION['login_redir'] = array($_AEYNIAS['parameters']['full_url'], time() + $minutes);   // set the URL as a session, this is called when the user logins so it can redirect accordingly..
			header('LOCATION: ' . $_AEYNIAS['config']['doc_url'] . '/login/');                          // and send them to login!
			exit;
			return false;                                                                               // it will never get this far, but for what ever reason (NEVEARR I SAY) we should tell the parent function not to continue.... pretty much a waste of bytes writing this line.
        }
        else {
            str_replace('!logged_in','',$this->_class['page']['text']['raw']);	// Replace the logged in text.
            return true;                                                        // the user does not have to be logged in, so we say "Go ahead" to the parent function.. in this case _load_page_text
        }
    }
    private function _strip_data(){
        $this->_class['page']['text']['parsed'] = $this->_class['page']['text']['raw'];                                                         // set up the main page text for modifying, so we can use the RAW for debugging.. we want raw unmodified.. IE cloning.. YEAH ok ill stop rambling..
        $this->_class['page']['text']['parsed'] = preg_replace('/@page_title = "(.*)"/','',$this->_class['page']['text']['parsed']);            // take out the @page_title
        $this->_class['page']['text']['parsed'] = preg_replace('/@css\s=\s\"(?:{.+}|[^\"])+\"/s','',$this->_class['page']['text']['parsed']);	// take out the @css.. Thanks Bokeh for the snippit.
        $this->_class['page']['text']['parsed'] = str_replace('!import_dynamic','',$this->_class['page']['text']['parsed']);	                // take out the !import_dynamic..
        $this->_class['page']['text']['parsed'] = str_replace('!dont_use_template','',$this->_class['page']['text']['parsed']);	                // take out the !dont_use_template..
        $this->_class['page']['text']['parsed'] = str_replace('!logged_in','',$this->_class['page']['text']['parsed']);	                        // take out the !dont_use_template..
        $this->_class['page']['text']['parsed'] = str_replace('!use_module_equivalent','',$this->_class['page']['text']['parsed']);	            // take out the !use_module_equivalent..
        $this->_class['page']['text']['parsed'] = $this->parse_functions($this->_class['page']['text']['parsed']);                              // Parse some predefined functions, this should only be called on static pages (As security measure..).
        $this->_class['page']['text']['parsed'] = trim($this->_class['page']['text']['parsed']);                                                // Strip out the extra lines made by deleting the AEYNIAS Special stuff ;)
    }
    function replace_error($spliced){
		global $errors;
		if ($errors == ''){
			/// strip the errors.
			$spliced = preg_replace('!<errors>((.*)[[:space:]]*)*</errors>!isU','',$spliced);
			$spliced = preg_replace('!%errors!','',$spliced);
		}
		else {
			// we have to show them.
			preg_match('!<errors>(((.*)[[:space:]]*)*)</errors>!isU',$spliced,$error_template);
			$spliced = preg_replace('!<errors>((.*)[[:space:]])*</errors>!isU','',$spliced);

			$error_template = $error_template[1];

			preg_match('!<error>(((.*)[[:space:]]*)*)</error>!isU',$error_template,$error_display_template);
			$error_display_template = $error_display_template[1];
			$error_template = preg_replace('!<error>((.*)[[:space:]]*)*</error>!isU','',$error_template);

			$disp_error = '';
			$num = 1;
			foreach ($errors as $the_error){
				$disp_error .= preg_replace(array('!%num!','!%error!'),array($num,$the_error),$error_display_template);
				$num++;
			}
			$error_template = preg_replace('!%show_errors!',$disp_error,$error_template);
			$spliced = preg_replace('!%errors!',$error_template,$spliced);

		}
		return $spliced;
	}
	function replace_praise($spliced){
		global $praises;
		if ($praises == ''){
			/// stip the errors.
			$spliced = preg_replace('!<praises>((.*)[[:space:]]*)*</praises>!isU','',$spliced);
			$spliced = preg_replace('!%praises!','',$spliced);
		}
		else {
			// we have to show them.
			preg_match('!<praises>(((.*)[[:space:]]*)*)</praises>!isU',$spliced,$praise_template);
			$spliced = preg_replace('!<praises>((.*)[[:space:]])*</praises>!isU','',$spliced);

			$praise_template = $praise_template[1];

			preg_match('!<praise>(((.*)[[:space:]]*)*)</praise>!isU',$praise_template,$praise_display_template);
			$praise_display_template = $praise_display_template[1];
			$praise_template = preg_replace('!<praise>((.*)[[:space:]]*)*</praise>!isU','',$praise_template);

			$disp_praise = '';
			$num = 1;
			foreach ($praises as $the_praise){
				$disp_praise .= preg_replace(array('!%num!','!%praise!'),array($num,$the_praise),$praise_display_template);
				$num++;
			}
			$praise_template = preg_replace('!%show_praises!',$disp_praise,$praise_template);
			$spliced = preg_replace('!%praises!',$praise_template,$spliced);


		}
		return $spliced;
	}
	function parse_functions($text){
		global $_AEYNIAS;
		$output = preg_replace('/#strip_html{(.*)}/e', "stripslashes(strip_tags('\\1'))", $text);
		$output = preg_replace('/#eval{(.*)}/e', "eval('\\1')", $output);
		$output = preg_replace('/@doc_url/', $_AEYNIAS['config']['doc_url'], $output);
		return $output;
	}
}

$aeynias = new _aeynias_template;

?>