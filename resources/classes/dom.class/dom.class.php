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
require('/Library/WebServer/Documents/aeynias_framework/resources/classes/dom.class/dom.element.properties.class/dom.element.properties.class.php');
class _propertyModifer{
    function __construct(){
        $this->properties = array();
    }
    function setProperties($properties){
        $this->properties = $properties;
        return $this;
    }
    
    function getPropertyByKey($key){
        if(array_key_exists($key,$this->properties))return $this->properties[$key];
        else return false;
    }
    
    function editProperty(){
        return call_user_func_array('self::addProperty',func_get_args());
    }
    
    function addProperty(){
        $args = func_get_args();
        if(count($args) == 1){
            $object = $args[0];
            if(is_object($object) && (get_class($object) == '_DOMElementProperty' || get_parent_class($object) == '_DOMElementProperty')){
                if(array_key_exists($object->getKey(),$this->properties))
                    $this->properties[$object->getKey()]->implode($object);
                else
                    $this->properties[$object->getKey()] = $object;
            }
            else if(is_array($object)){
                if(is_string($object[0]) && (is_string($object[1]) || is_bool($object[1]) || is_array($object[1]))){
                    switch(strtolower($object[0])){
                        case 'style':
                            $obj = new _DOMElementPropertyStyle($object[1]);
                        break;
                        case 'id':
                            $obj = new _DOMElementPropertyID($object[1]);
                        break;
                        default:
                            $obj = new _DOMElementProperty($object[0],$object[1]);
                        break;
                    }
                    $this->addProperty($obj);
                }
                else {
                    foreach($object as $possibility)$this->addProperty($possibility);
                }
            }
            else if(is_string($object))
                $this->addProperty(array($object,false));
        }
        else if(count($args) > 1){
            if(count($args) == 2 && (is_string($args[0]) && is_string($args[1]))){
                    $this->addProperty(array($args[0],$args[1]));
            }
            else
                foreach($args as $argument)$this->addProperty($argument);
        }
    }
}

class _DOMElement extends _propertyModifer{
    function __construct($tag = '', $forceID = true){
        parent::__construct();
        $this->tag = $tag;
        $this->data = null;
        $this->indentLevel = 0;
    }
    
    function setIndentLevel($indentLevel){
        $this->indentLevel= $indentLevel;
        return $this;
    }
    
    function getIndentLevel(){
        return $this->indentLevel;
    }
    
    function __call($functionName, $args){
        if(preg_match('/^(get|set|append_?)(.*)/', $functionName, $matches)){
            $propertyName = strtolower($matches[2]);
            $functionName = trim($matches[1],'_');
            if($functionName == 'get'){
                if(array_key_exists($propertyName,$this->properties) == true)
                    return $this->getPropertyByKey($propertyName)->getValue();
            }
            else if ($functionName == 'set'){
                $this->addProperty(array($propertyName,$args[0]));
                return $this;
            }
            else if($functionName == 'append'){
                $this->addProperty(array($propertyName,$args[0]));
                return $this;
            }
        }
        else trigger_error("Unknown method: $functionName()");
    }
    
    function setTag($tag){
        $this->tag = $tag;
        return $this;
    }
    
    function addData(){
        $args = func_get_args();
        foreach($args as $data){
            if(is_string($data))$this->data[] = html_entity_decode($data);
            else if(is_object($data) && get_class($data) == '_DOMElement'){
                $data->setIndentLevel($this->getIndentLevel() + 1);
                $this->data[] = $data->get();
            }
        }
        return $this;
    }
    
    function getDataOutputAtIndex($index){
        if(isset($this->data[$index]) && 
            (is_string($this->data[$index]) && $data = $this->data[$index]) || 
            (is_object($this->data[$index]) && $data = $this->data[$index]->get())
        ){
            foreach(explode("\n",$data) as $line){
                $indent = '';
                for($i = 0; $i <= $this->getIndentLevel(); $i++)$indent .= "\t";
                $lines[] = $indent . trim($line);
            }
            return implode("\n",$lines);
        }
    }
    
    function getDataAtIndex($index){
        return $this->data[$index];
    }
    
    function hasData(){
        return isset($this->data);
    }
    
    function get(){
        $properties = '';
        foreach($this->properties as $value)$properties .= (($output = $value->get()) != '') ? ' ' . html_entity_decode($output) : '';
        
        for($i = 1; $i <= $this->getIndentLevel(); $i++)$indent .= "\t";
        $element = $indent . '<' . $this->tag . $properties;
        if($this->hasData()){
            $element .= '>' . "\n";
            
            foreach($this->data as $index=>$value){
                $element .= $this->getDataOutputAtIndex($index);
                $element .= "\n";
            }
            
            $element .= $indent . '</' . $this->tag . '>';
        }
        else 
            $element .= '/>';
        return $element;
    }
    function createNew(){
        return clone($this);
    }
}
?>