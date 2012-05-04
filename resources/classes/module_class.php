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
class module {
    function me($absolute = false){
        global $_AEYNIAS;
        return $absolute == false ? $_AEYNIAS['config']['doc_url'] . '/' . $_AEYNIAS['parameters']['page'] : $_AEYNIAS['config']['doc_root'] . '/modules/' . $_AEYNIAS['parameters']['page'];
    }
    
    function show_text(){
        global $_AEYNIAS;
        if (isset($this->_class['points'])){
            foreach($this->_class['points'] as $key=>$value){
                if(count(array_diff($value,$_AEYNIAS['parameters']['extras'])) == 0 && method_exists($this,'page_' . $key)){
                    $this->{'page_' . $key}();
                    $skip = false;
                    break;
                }
                else $skip = true;
            }
        }
        
        $this->page_title = (isset($this->page_title)) ? $this->page_title : $_AEYNIAS['parameters']['page'];
        $this->page_text = (isset($this->page_text)) ? $this->page_text : '';
        $this->page_css = (isset($this->page_css)) ? $this->page_css : '';
        
        if ((count($_AEYNIAS['parameters']['extras']) == 0  || (isset($skip) && $skip == true)) && method_exists($this,'main'))$this->main();
        return array($this->page_title,$this->page_text,$this->page_css);
    }
    
    function associate_parameters($params,$point){
        $this->_class['points'][rtrim($point,'()')] = $params;
    }
    
    function is_admin(){
        global $_AEYNIAS;
        if ($_AEYNIAS['authentication']['admin']['logged_in'] === false){
            $minutes = 3;   // Amount of minutes until the session expires.. Checked within Login.php.. I suppose I could just use cookies, yeah?...
    		$minutes = $minutes * 60;
    		$_SESSION['login_redir'] = array($_AEYNIAS['parameters']['full_url'], time() + $minutes);                    // set the URL as a session, this is called when the user logins so it can redirect accordingly..
    		header('LOCATION: ' . $_AEYNIAS['config']['doc_url'] . '/login/');            // and send them to login!
    		exit;
    		return false;
    	}
    	else return true;
    }
    
    function is_logged_in(){
        global $_AEYNIAS;
        if ($_AEYNIAS['authentication']['user']['logged_in'] === false){
            $minutes = 3;   // Amount of minutes until the session expires.. Checked within Login.php.. I suppose I could just use cookies, yeah?...
    		$minutes = $minutes * 60;
    		$_SESSION['login_redir'] = array($_AEYNIAS['parameters']['full_url'], time() + $minutes);                    // set the URL as a session, this is called when the user logins so it can redirect accordingly..
    		header('LOCATION: ' . $_AEYNIAS['config']['doc_url'] . '/login/');            // and send them to login!
    		exit;
    		return false;
    	}
    	else return true;
    }
    
}
?>