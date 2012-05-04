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
class _DOMElementProperty{
    function __construct($key = '', $value = ''){
        $this->key = strtolower($key);
        $this->value = $value;
    }
    
    function appendValue($value){
        $this->value .= $value;
        return $this;
    }
    
    function setValue($value){
        $this->value = $value;
        return $this;
    }
    
    function getValue(){
        return $this->value;
    }
    
    function getKey(){
        return $this->key;
    }
    
    function implode($object){
        if(is_object($object) && get_class($object) == '_DOMElementProperty' || get_parent_class($object) == '_DOMElementProperty')
            $this->appendValue($object->getValue());
        else user_error('You cannot implode two different objects. Sorry.');
    }
    
    function get(){
        if($this->value !== false && $this->value != '')
            return $this->key . '="' . str_replace('"','\"',$this->value) . '"';
        else if($this->value === false)return $this->key;
        else return '';
    }
}

class _DOMElementPropertyID extends _DOMElementProperty{
    function __construct($id = ''){
        $this->key = 'id';
        $this->value = $id;
        if($id==''){
            global $_DOMElementPropertyUniqueID;
            if(!is_numeric($_DOMElementPropertyUniqueID))$_DOMElementPropertyUniqueID = 0;
            $this->value = substr(md5(rand(0,10) . 'element' . ++$_DOMElementPropertyUniqueID),0,7);
        }
    }
}

class _DOMElementPropertyStyle extends _DOMElementProperty{
    function __construct($styles = ''){
        $this->key = 'style';
        $this->styles = array();
        $this->setStyle($styles);
    }
    
    function getStyleByKey($key){
        return $this->styles[$key];
    }
    
    private function processStyleString($string){
        $string = trim($string);
        $styleArray = array();
        $styleStringsArray = explode(';',trim($string,';'));
        foreach($styleStringsArray as $styleString){
            $styleEntity = explode(':',$styleString);
            $key = trim($styleEntity[0]);
            $value = trim($styleEntity[1]);
            $styleArray[$key] = $value;
        }
        return $styleArray;
    }
    function setStyle(){
        $args = func_get_args();
        if(count($args) == 1){
            $value = $args[0];
            if(is_array($value)){
                if(count($value) == 1)
                    $this->setStyle($value[0]);
                else if(count($value) == 2 && !is_array($value[0]))
                    $styles[trim($value[0],': ')] = trim($value[1],': ');
                else
                    foreach($value as $style)$this->setStyle($style);
            }
            else if(is_string($value) && $value != '')$styles = $this->processStyleString($value);
        }
        else if(count($args) == 2 && (is_string($args[0]) && is_string($args[1])))
            $styles[trim($args[0],': ')] = trim($args[1],': ');
        if(count($styles) > 0)$this->styles = array_merge($this->styles,$styles);
        return $this;
    }
    function appendValue($value){
        return $this->setStyle($value);
    }
    
    function setValue(){
        return call_user_func_array('self::setStyle',func_get_args());
    }
    
    function getStyles(){
        $styles = '';
        foreach($this->styles as $key=>$value)$styles .= $styles == '' ? $key .': ' . $value . ';' : ' ' . $key .': ' . $value . ';';
        return $styles;
    }
    
    function getValue(){
        return $this->getStyles();
    }
    
    function get(){
        $return = $this->key . '="' . $this->getStyles() . '"';
        return $return;
    }
}
?>