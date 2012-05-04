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
function get_key($array,$key){
    return $array[$key];
}
class _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'input';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->mySQLColumn = null;
    }
    
    function setMySQLColumn($col){
        if(get_class($col) == 'mySQLColumn')
            $this->mySQLColumn = $col;
        return $this;
    }
    
    function setAttributes($attr){
        foreach($attr as $key=>$value)$this->setup[$key] = $value;
        return $this;
    }
    
    function carryOverValue(){
        return $this->carryOverValue;
    }
    
    function setName($name){
        $this->setup['name'] = $name;
        return $this;
    }
    
    function setValue($value){
        $this->setup['value'] = $value;
        return $this;
    }
    
    function getValue(){
        if($this->carryOverValue() && isset($_POST[$this->getName()]))return $_POST[$this->getName()];
        else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())return get_key(current($this->mySQLColumn->Table->getRows(true)),$this->mySQLColumn->Field);
        else return (isset($this->setup['value'])) ? $this->setup['value'] : '';
    }
    
    function getType(){
        return $this->fieldType;
    }
    
    function getName(){
        global $_AEYNIASClassForm_nextFormIncriment;
        if($_AEYNIASClassForm_nextFormIncriment == '')$_AEYNIASClassForm_nextFormIncriment = 0;
        if(!isset($this->setup['name'])){
            if(isset($this->mySQLColumn))$this->setup['name']=substr(md5($this->mySQLColumn->Field),0,5);
            else $this->setup['name'] = substr(md5(++$_AEYNIASClassForm_nextFormIncriment),0,5);
        }
        return $this->setup['name'];
    }
    
    function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    function getLabel(){
        return $this->label;
    }
    
    function makeRequired($required_text = ''){
        if ($required_text == '')$required_text = "Please check that " . strToLower($this->label) . " is completely filled out.";
        $this->evaluateExpressions[] = array('expression'=>'if(isset($_POST[$name]) && $_POST[$name] != "")return true; else return false;','message'=>$required_text);
        return $this;
    }
    
    function addExpressionCheck($expression_array){
        $this->evaluateExpressions[] = $expression_array;
        return $this;
    }
    
    function getExpressionsToCheck(){
        return $this->evaluateExpressions;
    }
    
    function disableCarryOver(){
        $this->carryOverValue = false;
        return $this;
    }
    
    function enableCarryOver(){
        $this->carryOverValue = true;
        return $this;
    }
    
    function getAttributes(){
        return $this->setup;
    }
    
    function getContentsHTML(){
        $input_contents = '';
        if(!isset($this->setup['value']))
            if($value = $this->getValue() != '')$this->setup['value'] = $value;
            
        if(!isset($this->setup['name']))
            $this->getName();
            
        foreach($this->setup as $key=>$value){
            if($key == 'value')$value = $this->getValue();
            $input_contents .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        echo $input_contents . "<br>";
        return $input_contents;
    }
    
    function hasExpressionCheck(){
        if (count($this->evaluateExpressions) != 0)return true;
        else return false;
    }
    
    function createNew(){
        return clone($this);
    }
    function encapsulateMe($tag = 'div'){
        $encapsulator = new _AEYNIASformEncapsulator;
        $encapsulator->setTag($tag)->addFields(array($this));
        return $encapsulator;
    }
    
}

class _AEYNIASformClassGenericHidden extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'hidden';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->isArray = false;
    }
}

class _AEYNIASformClassGenericPassword extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'password';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->isArray = false;
    }
}

class _AEYNIASformClassGenericSubmit extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'submit';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->isArray = false;
    }
}

class _AEYNIASformClassGenericCheckBox extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'checkbox';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->isArray = false;
        $this->checked = false;
    }
    
    function makeArray(){
        // for the actual input style, ya know, like name=mycheckboxes[]
        $this->isArray = true;
        return $this;
    }
    
    function isArray(){
        return $this->isArray;
    }
    
    function getName($withArray=true){
        if($this->isArray && $withArray)return parent::getName() . '[]';
        else return parent::getName();
    }
    
    function setChecked($boolean = true){
        $this->checked = $boolean;
        return $this;
    }
    
    function getContentsHTML(){
        $input_contents = parent::getContentsHTML();
        if(
            (
                $this->checked
                &&
                $this->carryOverValue == false
            )
            ||
            (
                $this->checked
                &&
                $this->carryOverValue == true
                &&
                count($_POST) == 0
            )
            ||
            (
                $this->carryOverValue == true
                    &&
                (
                    (
                        $this->isArray()
                        &&
                        in_array($this->getValue(),$_POST[$this->getName(false)])
                    )
                    ||
                    isset($_POST[$this->getName(false)])
                )
            )
        )$input_contents .= " checked";
        return $input_contents;
    }
}

class __AEYNIASformClassGenericCheckBox extends _AEYNIASformClassGenericCheckbox{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'checkbox';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->returnTo = null;
    }
    function setReturnTo($object){
        $this->returnTo = $object;
        return $this;
    }
    
    function appendOption(){
        $this->returnTo->addFields(array($this));
        return $this->returnTo;
    }
}

class _AEYNIASformClassGenericCombinationCheckBox extends _AEYNIASformEncapsulator{
    function __construct(){
        $this->fields = array();
        $this->fieldType = "checkbox_encapsulator";
        $this->tag = '';
        $this->evaluateExpressions = array();
        $this->otherSetup = array('name'=>'');
        $this->setup = array();
        $this->label = '';
    }
    
    function addOption($array = null){
        $newOption = new __AEYNIASformClassGenericCheckBox;
        $newOption->setReturnTo($this)->setName($this->otherSetup['name'])->makeArray();
        return $newOption;
    }
    
    function getName(){
        return $this->otherSetup['name'];
    }
    
    function setName($name){
        $this->otherSetup['name'] = $name;
        return $this;
    }
    
    function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    function getLabel(){
        return $this->label;
    }
    
    function makeRequired($required_text = ''){
        if ($required_text == '')$required_text = "Please check that " . $this->label . " is completely filled out.";
        $this->evaluateExpressions[] = array('expression'=>'if(count($_POST[\''.$this->getName() .'\']) == 0)return false; else return true;','message'=>$required_text);
        return $this;
    }
    
}

class _AEYNIASformClassGenericRadio extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'radio';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->checked = false;
    }
    function setChecked($boolean = true){
        $this->checked = $boolean;
        return $this;
    }
    function getValue(){
        return isset($this->setup['value']) ? $this->setup['value'] : '';
    }
    
    function getContentsHTML(){
        $input_contents = parent::getContentsHTML();
        if(
            (
                $this->checked
                &&
                $this->carryOverValue == false
            )
            ||
            (
                $this->checked
                &&
                $this->carryOverValue == true
                &&
                count($_POST) == 0
            )
            ||
            (
                (
                    $this->carryOverValue == true
                    && 
                    isset($_POST[$this->getName()])
                )
                &&
                $_POST[$this->getName()] == $this->getValue()
            )
        )$input_contents .= " checked";
        return $input_contents;
    }
    
}

class _AEYNIASformClassGenericTextArea extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'textarea';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->value = '';
    }
    
    function setRows($rows){
        $this->setup['rows'] = $rows;
        return $this;
    }
    
    function setCols($cols){
        $this->setup['cols'] = $cols;
        return $this;
    }
    
    function getRows(){
        return $this->setup['rows'];
    }
    
    function getCols(){
        return $this->setup['cols'];
    }
    
    function setAttributes($attr){
        foreach($attr as $key=>$value){
            if(strtolower($key) == 'value')$this->value = $value;
            else $this->setup[$key] = $value;
        }
        return $this;
    }
    
    function setValue($value){
        $this->value = $value;
        return $this;
    }
    
    function getValue(){
        if($this->carryOverValue() && isset($_POST[$this->getName()]))return $_POST[$this->getName()];
        else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())return get_key(current($this->mySQLColumn->Table->getRows()),$this->mySQLColumn->Field);
        else return $this->value;
    }
}

class __AEYNIASformClassGenericRadio extends _AEYNIASformClassGenericRadio{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = 'radio';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        $this->returnTo = null;
        $this->checked = false;
    }
    function setReturnTo($object){
        $this->returnTo = $object;
        return $this;
    }
    
    function appendOption(){
        $this->returnTo->addFields(array($this));
        return $this->returnTo;
    }
    
    function getName(){
        $name = $this->returnTo->getName();
        $this->setup['name'] = $name;
        return $name;
    }
    
    function getValue(){
        return isset($this->setup['value']) ? $this->setup['value'] : '';
    }
}

class _AEYNIASformClassGenericCombinationRadio extends _AEYNIASformEncapsulator {
    function __construct(){
        $this->fields = array();
        $this->fieldType = "radio_encapsulator";
        $this->tag = '';
        $this->evaluateExpressions = array();
        $this->otherSetup = array();
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true;
        
        $this->mySQLColumn = null;
    }
    
    function carryOverValue(){
        return $this->carryOverValue;
    }
    function disableCarryOver(){
        $this->carryOverValue = false;
        return $this;
    }
    
    function enableCarryOver(){
        $this->carryOverValue = true;
        return $this;
    }
    
    function setMySQLColumn($col){
        if(get_class($col) == 'mySQLColumn')
            $this->mySQLColumn = $col;
        return $this;
    }
    
    function addOption(){
        $newOption = new __AEYNIASformClassGenericRadio;
        $newOption->setReturnTo($this);
        return $newOption;
    }
    
    function newOption(){
        return $this->addOption();
    }
    
    function getName(){
        global $_AEYNIASClassForm_nextFormIncriment;
        if($_AEYNIASClassForm_nextFormIncriment == '')$_AEYNIASClassForm_nextFormIncriment = 0;
        if(!isset($this->otherSetup['name'])){
            if(isset($this->mySQLColumn))$this->otherSetup['name']=substr(md5($this->mySQLColumn->Field),0,5);
            else $this->otherSetup['name'] = substr(md5(++$_AEYNIASClassForm_nextFormIncriment),0,5);
        }
        return $this->otherSetup['name'];
    }
    
    function setName($name){
        $this->otherSetup['name'] = $name;
        return $this;
    }
    
    function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    function getLabel(){
        return $this->label;
    }
    
    function makeRequired($required_text = ''){
        if ($required_text == '')$required_text = "Please check that " . $this->label . " is completely filled out.";
        $this->evaluateExpressions[] = array('expression'=>'if(isset($_POST[\''.$this->getName().'\']))return true; else return false;','message'=>$required_text);
        return $this;
    }
    
}

class _AEYNIASformClassGenericSelect extends _AEYNIASformClassGenericField{
    function __construct(){
        $this->evaluateExpressions = array();
        $this->fieldType = "select";
        $this->value = '';
        $this->validateWithRegEx = false;
        $this->setup = array();
        $this->label = '';
        $this->carryOverValue = true; 
    }
    function setOptions($options){
        $this->options = $options;
        return $this;
    }
    
    function getOptions(){
        return $this->options;
    }
    
    function setValue($value){
        $this->value = $value;
        return $this;
    }
    
    function getValue(){
        if($this->carryOverValue() && isset($_POST[$this->getName()]))return $_POST[$this->getName()];
        else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())return get_key(current($this->mySQLColumn->Table->getRows()),$this->mySQLColumn->Field);
        else return $this->value;
    }
    
    function setAttributes($attr){
        foreach($attr as $key=>$value)$this->setup[$key] = $value;
        return $this;
    }
}

class form_creator{
    function __construct($options = array()){
        global $_AEYNIAS;
        $this->method = (isset($options['method'])) ? $options['method'] : 'post';
        $this->action = (isset($options['action'])) ? $options['action'] : $_AEYNIAS['parameters']['full_url'];
        $this->fields = array();
        $this->validationFields = array();
        $this->name = null;
        
        $this->mySQLTable = null;
    }
    
    function setName($name){
        $this->name = $name;
        return $this;
    }
    
    function setMySQLTable($table){
        if(get_class($table) == 'mySQLTable')
            $this->mySQLTable = $table;
        return $this;
    }
    
    function explodeClasses(){
        return array('generic' => new _AEYNIASformClassGenericField, 'input' => new _AEYNIASformClassGenericField, 'hidden'=> new _AEYNIASformClassGenericHidden, 'select' => new _AEYNIASformClassGenericSelect, 'validator' => new _AEYNIASformClassValidator($this), 'encapsulator' => new _AEYNIASformEncapsulator, 'checkbox' => new _AEYNIASformClassGenericCheckBox, 'combinationCheckbox' => new _AEYNIASformClassGenericCombinationCheckBox, 'combinationRadio' => new _AEYNIASformClassGenericCombinationRadio, 'radio' => new _AEYNIASformClassGenericRadio, 'submit' => new _AEYNIASformClassGenericSubmit, 'password'=> new _AEYNIASformClassGenericPassword, 'textarea' => new _AEYNIASformClassGenericTextArea);
    }
    
    function addFields($fields,$forValidationOnly = false){
        foreach($fields as $field){
            if($forValidationOnly == true)$this->validationFields[] = $field;
            else {
                $this->validationFields[] = $field;
                $this->fields[] = $field;
            }
            if (strpos($field->getType(),'encapsulator')){
                $this->addFields($field->getFields(),true);
            }
        }
    }
    
    function getFields(){
        return $this->validationFields;
    }
    function getName(){
        if(!isset($this->name) && isset($this->mySQLTable))
            $this->name = substr(md5($this->mySQLTable->mySQLTable),0,5);
        else if(!isset($this->name)){
            $names = '';
            foreach($this->fields as $field)
                $names .= $field->getName();
            $this->name = substr(md5($names),0,5);
        }
        return $this->name;
    }
    function processFields($fieldsToProcess){
        $form_text = '';
        foreach($fieldsToProcess as $field){
            switch($field->getType()){
                case 'input':
                    $form_text .= '<label>' . $field->getLabel() . '</label><input type="text"' . $field->getContentsHTML() .'><br>' . "\n";
                break;
                case 'hidden':
                    $form_text .= '<input type="hidden"' . $field->getContentsHTML() .'><br>' . "\n";
                break;
                case 'password':
                    $form_text .= '<label>' . $field->getLabel() . '</label><input type="password"' . $field->getContentsHTML() .'><br>' . "\n";
                break;
                case 'submit':
                    $form_text .= '<input type="submit"' . $field->getContentsHTML() .'><br>' . "\n";
                break;
                case 'textarea':
                    $form_text .= '<label>' . $field->getLabel() . '</label><textarea' . $field->getContentsHTML() .'>'.$field->getValue().'</textarea><br>' . "\n";
                break;
                case 'checkbox':
                    $form_text .= '<label>' . $field->getLabel() . '</label><input type="checkbox"' . $field->getContentsHTML() .'><br>' . "\n";
                break;
                case 'radio':
                   $form_text .= '<label>' . $field->getLabel() . '</label><input type="radio"' . $field->getContentsHTML() . '><br>' . "\n";
                break;
                case 'select':
                    $options = '';
                    foreach($field->getOptions() as $value=>$text){
                        if(is_array($text)){
                            $options .= '<optgroup label="' . $value .'">';
                            foreach($text as $value=>$text){
                                $selected = $field->getValue() == $value ? ' selected' : '';
                                $options .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($text) . '</option>';
                            }
                            $options .= '</optgroup>';
                        }
                        else {
                            $selected = $field->getValue() == $value ? ' selected' : '';
                            $options .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($text) . '</option>';
                        }
                    }
                    $form_text .= '<label>' . $field->getLabel() . '</label><select' . $field->getContentsHTML() .'>'.$options.'</select><br>' . "\n";
                break;
                case 'encapsulator':
                    $fields = $this->processFields($field->getFields());
                    $tag_contents = '';
                    $tag = $field->getTag();
                    foreach($fieldAttributes as $key=>$value)$tag_contents .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
                    if ($tag === '')$form_text .= $fields;
                    else $form_text .= '<' . $tag . $tag_contents .'>' . $fields . '</' . $tag . '>';
                break;
                case 'checkbox_encapsulator':
                    $fields = $this->processFields($field->getFields());
                    $tag_contents = '';
                    $pre = '<label>' . $field->getLabel() . '</label>';
                    $tag = $field->getTag();
                    foreach($fieldAttributes as $key=>$value)$tag_contents .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
                    if ($tag === '')$form_text .= $pre . $fields;
                    else $form_text .= $pre . '<' . $tag . $tag_contents .'>' . $fields . '</' . $tag . '><br>';
                break;
                case 'radio_encapsulator':
                    $fields = $this->processFields($field->getFields());
                    $tag_contents = '';
                    $pre = '<label>' . $field->getLabel() . '</label>';
                    $tag = $field->getTag();
                    if ($tag === '')$form_text .= $pre . $fields;
                    else $form_text .= $pre . '<' . $tag . $field->getContentsHTML() .'>' . $fields . '</' . $tag . '><br>';
                break;
            }
        }
        return $form_text;
    }
    
    function printForm(){
        $form_text = '<form method="'.$this->method.'" action="'.$this->action.'"><input type="hidden" name="_AEYNIASFormName" value="'.$this->getName().'">';
        $form_text .= $this->processFields($this->fields);
        $form_text .= '</form>';
        return $form_text;
    }
}

class _AEYNIASformEncapsulator {
    function __construct(){
        $this->fields = array();
        $this->fieldType = "encapsulator";
        $this->tag = '';
        $this->evaluateExpressions = array();
        $this->setup = array();
    }
    
    function getFields(){
        return $this->fields;
    }
    
    function addFields($fields){
        foreach($fields as $field)$this->fields[] = $field;
        return $this;
    }
    
    function setTag($tag){
        $this->tag = $tag;
        return $this;
    }
    
    function getTag(){
        return $this->tag;
    }
    
    function getType(){
        return $this->fieldType;
    }
    
    function createNew($tag = 'div'){
        $return_object = clone($this);
        $return_object->setTag($tag);
        return $return_object;
    }
    
    function addExpressionCheck($expression_array){
        $this->evaluateExpressions[] = $expression_array;
        return $this;
    }
    
    function getExpressionsToCheck(){
        return $this->evaluateExpressions;
    }
    
    function getAttributes(){
        return $this->setup;
    }
    
    function hasExpressionCheck(){
        if (count($this->evaluateExpressions) != 0)return true;
        else return false;
    }
    
    function setAttributes($attr){
        foreach($attr as $key=>$value){
            if($key == 'class' && $this->setup['class'] != '')$this->setup[$key] .= ' ' . $value;
            else $this->setup[$key] = $value;
        }
        return $this;
    }
    
    function getContentsHTML(){
        $input_contents = '';
        foreach($this->setup as $key=>$value){
            $input_contents .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        return $input_contents;
    }
    
    function encapsulateMe($tag = 'div'){
        $encapsulator = new _AEYNIASformEncapsulator;
        $encapsulator->setTag($tag)->addFields(array($this));
        return $encapsulator;
    }
}

class _AEYNIASformClassValidator{
    function __construct($form){
        $this->fields = array();
        $this->custom_arguments = array();
        $this->breakWithOneField = true;
        $this->formName = null;
        $this->form = $form;
    }
    
    function setFields(){
        $fields = $this->form->getFields();
        $this->formName = $this->form->getName();
        foreach ($fields as $field){
            if($field->hasExpressionCheck())$this->fields[] = $field;
        }
        return $this;
    }
    
    function enableErrorAllAtOnce(){
        $this->breakWithOneField = false;
        return $this;
    }
    
    function disableErrorAllAtOnce(){
        $this->breakWithOneField = true;
        return $this;
    }
    
    function applyArgumentsToRequiredFields($args){
        $this->custom_arguments = $args;
        return $this;
    }
    
    function arrayOfFieldNames(){
        if (count($this->fields) === 0)error('Cannot grab fields, because the fields have not been set.');
        else {
            $fieldNames = array();
            foreach($this->fields as $field){
                $fieldInformation = $field->getAttributes();
                $fieldNames[$fieldInformation['name']] = $field;
            }
        }
        return $fieldNames;
    }
    
    function checkForm($message = null){
        $this->setFields();
        $errorHasBeenSet = false;
        global $_AEYNIAS;
        if (count($this->fields) > 0){
            if(isset($_POST['_AEYNIASFormName']) && $_POST['_AEYNIASFormName'] == $this->form->getName()){
                foreach($this->fields as $field){
                    $name = $field->getName();
                    foreach ($field->getExpressionsToCheck() as $expression){
                        if (!eval($expression['expression'])){
                            error($expression['message']);
                            $errorHasBeenSet = true;
                            $field->setAttributes($this->custom_arguments);
                            break;
                        }
                    }
                    if($errorHasBeenSet && $this->breakWithOneField)break;
                }
                if(!$errorHasBeenSet){
                    if ($message != null)praise($message);
                    return true;
                }
                else return false;
            }
        }
    }    
}
?>