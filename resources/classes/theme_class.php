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
class theme {
    function grab_theme_backend($name, $referenced = false){
        global $_AEYNIAS;
        if ($this->check_if_theme_exists($name . '/') && $name != null){
            $theme_info = file_get_contents($_AEYNIAS['config']['doc_root'] . '/themes/' . $name . '/info.sid');
            
            preg_match('!<title>(.*)</title>!',$theme_info,$theme_title);
            $theme_title = trim($theme_title[1]);
            
            preg_match('!<description>(.*)</description>!',$theme_info,$theme_description);
            $theme_description = trim($theme_description[1]);
            
            preg_match('!<html>(.*)</html>!s',$theme_info,$theme_html);
            $theme_html = str_replace('@here',$_AEYNIAS['config']['doc_url'] . '/themes/' . $name . '',trim($theme_html[1]));
            return array($theme_title,$theme_description,$theme_html,$name,true,'title'=>$theme_title,'descripton'=>$theme_description,'html'=>$theme_html,'folder'=>$name,'status'=>true);
            
        }
        else if(isset($_AEYNIAS['config']['default_theme']) && $referenced == false)return $this->grab_theme_backend($_AEYNIAS['config']['default_theme'],true);
        else {
            $all_themes = $this->grab_all_themes();
            if (count($all_themes) > 0)return $this->grab_theme_backend(key($all_themes),true);
            else return array('','','','',false,'title'=>'','description'=>'','html'=>'','folder'=>'','status'=>false);
        }
    }
    
    function grab_theme($name=null){
        global $_AEYNIAS;
        if ($name == null){
            if ($_AEYNIAS['authentication']['user']['logged_in'] == 1)return $this->grab_theme_backend($_AEYNIAS['authentication']['user']['theme']);
            else if (isset($_AEYNIAS['config']['']))return $this->grab_theme_backend($_AEYNIAS['config']['default_theme'],true);
            else return $this->grab_theme_backend(null,true);
        }
        else return $this->grab_theme_backend($name);
    }
    
    function grab_all_themes(){
        global $_AEYNIAS;
        $dir = $_AEYNIAS['config']['doc_root'] . '/themes/';
        if (is_dir($dir)){
            $directory = opendir($dir);
            while(($file = readdir($directory)) !== false){
                if ($file != "." && $file != ".." && is_dir($dir . $file . '/')){
                    $theme_info = $this->grab_theme_backend($file);
                    $themes[$file] = $theme_info['title'];
                }
            }
            closedir($directory);
            return $themes;
        }
    }
    
    function check_if_theme_exists($theme){
        global $_AEYNIAS;
        if (is_dir($_AEYNIAS['config']['doc_root'] . '/themes/' . $theme . '/') && is_file($_AEYNIAS['config']['doc_root'] . '/themes/' . $theme . '/info.sid'))return true;
        else return false;
    }
}
$_AEYNIAS['class']['theme'] = new theme;

?>
