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

require('./dom.class/dom.class.php');
class formClass{
    function __construct(){
        $this->container = new DOMContainerWithValidation('form');
        $this->formName = getPredefinedFormName();
        $hidden = new formClassFieldHidden;
        $hidden->setName('formName')->setValue($this->formName);
        $this->container->addData($hidden);
    }
    
    function getContainer(){
        return $this->container;
    }
    
    function get(){
        if(isset($_POST['formName']) && $_POST['formName'] == $this->formName){
            $args = $this->container->getAllValidationArguments();
            foreach($args as $arg){
                $arg->validateAll();
            }
        }
        return $this->container->get();
    }
    
}

class formClassValidationArgument extends _propertyModifer{
    function __construct($belongsTo){
        parent::__construct();
        $this->belongsTo = $belongsTo;
        $this->function = function(){};
        $this->message = '';
    }
    
    function escape(){
        return $this->belongsTo;
    }
    
    function setMessage($message){
        $this->message = $message;
        return $this;
    }
    
    function getMessage(){
        return $this->message;
    }
    
    function setFunction($function){
        if(is_object($function) && get_class($function) == 'Closure')
            $this->function = $function;
        else if(is_string($function) && is_subclass_of($this->belongsTo->belongsTo,'formClassFieldTemplate')){
            switch(strtolower($function)){
                case 'isvalidemail':
                    $this->function = function($array){
                        return preg_match('!\S{3,}@\S{3,}.\S{1,}!',$array['field']) == 1 ? true : false;
                    };
                break;
                case 'isnull':
                    $this->function = function($array){
                        return is_null($array['field']);
                    };
                break;
                case 'isnotnull':
                    $this->function = function($array){
                        return !is_null($array['field']);
                    };
                break;
                case 'isnumeric':
                    $this->function = function($array){
                        return is_numeric($array['field']);
                    };
                break;
                case 'isnotnumeric':
                    $this->function = function($array){
                        return !is_numeric($array['field']);
                    };
                break;
                case 'required':
                    $this->function = function($array){
                        if(isset($array['field']) && $array['field'] == '')return false;
                        else if (!isset($array['field']))return false;
                        else return true;
                    };
                break;
            }
        }
        return $this;
    }
    
    function validate(){
        if(is_subclass_of($this->belongsTo->belongsTo,'formClassFieldTemplate')){
            $array = array(
                    'fieldName' => $this->escape()->escape()->getName(),
                    'fieldValue' => $this->escape()->escape()->getValue(),
                    'field' => isset($_POST[$this->escape()->escape()->getName()]) ? $_POST[$this->escape()->escape()->getName()] : null
                );
            $return = call_user_func($this->function,$array);
        }
        else $return = call_user_func($this->function);
        return $return;
    }
}

class formClassValidationArgumentsContainer{
    function __construct($belongsTo){
        $this->arguments = array();
        $this->belongsTo = $belongsTo;
    }
    
    function getValidationArguments(){
        return $this->arguments;
    }
    
    function newValidationArgument(){
        return $this->arguments[] = new formClassValidationArgument($this);
    }
    
    function validateAll(){
        $returnValue = true;
        $error = '';
        foreach($this->arguments as $argument){
            if($argument->validate() === false){
                $returnValue = false;
                $error = $argument->getMessage();
                break;
            }
        }
        if($error != '')echo $error . "<br>";
        return $returnValue;
    }
    
    function escape(){
        return $this->belongsTo;
    }
}

function getPredefinedFormName(){
    global $aeyniasformnamenumber;
    if(is_numeric($aeyniasformnamenumber)){
        return substr(md5('form' . ++$aeyniasformnamenumber),0,8);
    }
    else {
        $aeyniasformnamenumber = 0;
        return getPredefinedFormName();
    }
}

function getPredefinedFormFieldName(){
    global $aeyniasformfieldnamenumber;
    if(is_numeric($aeyniasformfieldnamenumber)){
        return substr(md5('formField' . ++$aeyniasformfieldnamenumber),0,8);
    }
    else {
        $aeyniasformfieldnamenumber = 0;
        return getPredefinedFormFieldName();
    }
}

class DOMContainerWithValidation extends _DOMElement{
    function __construct($tag = '',$forceID = true){
        parent::__construct($tag, $forceID);
        $this->validationArgumentsContainer = new formClassValidationArgumentsContainer($this);
    }
    
    function getValidationArguments(){
        return $this->validationArgumentsContainer;
    }
    
    function getAllValidationArguments(){
        $arguments[] = $this->getValidationArguments();
        foreach($this->data as $field){
            if(get_class($field) == 'DOMContainerWithValidation'){
                $arguments += $field->getAllValidationArguments();
            }
            else {
                $arguments[] = $field->getValidationArguments();
            }
        }
        return $arguments;
    }
    
    function addData(){
        $args = func_get_args();
        foreach($args as $data){
            if(is_object($data) && (is_subclass_of($data,'formClassFieldTemplate') || is_subclass_of($data,'_DOMElement')))$this->data[] = $data;
            else if (is_string($data))parent::addData($data);
        }
        return $this;
    }
}

class formClassFieldTemplate{
    function __construct(){
        $this->fieldValue='';
        $this->fieldName=getPredefinedFormFieldName();
        $this->carryOverValue = true;
        $this->fieldLabel = null;
        $this->validationArgumentsContainer = new formClassValidationArgumentsContainer($this);
        $this->mySQLColumn = null;
        $this->outputTemplate = '%label: %field<br>';
        $this->type = 'formField';
    }
    
    function setValue($value){
        $this->fieldValue = $value;
        return $this;
    }
    
    function getValue(){
        if($this->carryOverValue && isset($_POST[$this->fieldName]))
            return $_POST[$this->fieldName];
        else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())
            return get_key(current($this->mySQLColumn->Table->getRows(true)),$this->mySQLColumn->Field);
        else return $this->fieldValue;
    }
    
    function setName($name){
        $this->fieldName=$name;
        return $this;
    }
    
    function getName(){
        return $this->fieldName;
    }
    
    function setLabel($label){
        if(is_string($label)){
            $newLabel = new _DOMElement('label');
            $newLabel->addData($label);
            $label = $newLabel;
        }
        if(is_object($label) && get_class($label) == '_DOMElement')
            $this->fieldLabel = $label;
        return $this;
    }
    
    function toggleCarryOverValue(){
        $this->carryOverValue = $this->carryOverValue == true ? false : true;
        return $this;
    }
    
    function enableCarryOverValue(){
        $this->carryOverValue = true;
        return $this;
    }
    
    function disableCarryOverValue(){
        $this->carryOverValue = false;
        return $this;
    }
    
    function getValidationArguments(){
        return $this->validationArgumentsContainer;
    }
    
    function setMySQLColumn($column){
        if(get_class($column) == 'mySQLColumn')
            $this->mySQLColumn = $column;
        return $this;
    }
    
    function getDOMElement(){
        return $this->DOMElement;
    }
    
    function setOutputTemplate($template){
        $this->outputTemplate = $template;
        return $this;
    }
    
    function makeRequired($text = null){
        if($text == null)$text = 'Field "' . $this->getLabelText() . '" is required!';
        $this->getValidationArguments()->newValidationArgument()->setFunction('required')->setMessage($text);
        return $this;
    }
    
    function getLabelText(){
        return $this->fieldLabel->getDataAtIndex(0);
    }
    
    function __call($functionName, $args){
        if(preg_match('/^([gs]et_?)(.*)/', $functionName, $matches)){
            $return = call_user_func_array(array(&$this->DOMElement,$functionName),$args);
            if(rtrim($matches[1], '_') == 'set')return $this;
            else return $return;
        }
        else trigger_error("Unknown method: $functionName()");
    }
    
    function getDOMLabel(){
        if(isset($this->fieldLabel))return $this->fieldLabel->get();
        else return '';
    }
    
    function get(){
        list($label,$field) = $this->preflightGet();
        return preg_replace(array('/%label/','/%field/'),array($label,$field),$this->outputTemplate);
    }
}

class formClassFieldSubmit extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('input');
        $this->DOMElement->setType('submit');
        $this->outputTemplate = '%field';
    }
    function preflightGet(){
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldText extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('input');
        $this->DOMElement->setType('text');
    }
    function preflightGet(){
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldPassword extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('input');
        $this->DOMElement->setType('password');
    }
    function preflightGet(){
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldHidden extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('input');
        $this->DOMElement->setType('hidden');
        $this->outputTemplate = '%field';
    }
    function preflightGet(){
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldTextArea extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('textarea');
    }
    function preflightGet(){
        $this->DOMElement->addData($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldCheckbox extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('input');
        $this->DOMElement->setType('checkbox');
        $this->carryOverValue = false;
    }
    
    function setBoolean($value){
        $this->toggled = $value;
        return $this;
    }
    
    function enableCarryOverValue(){
        return $this;
    }
    
    function getBool(){
        if($this->carryOverValue == true && count($_POST) > 0 && isset($_POST[$this->getName()]))
            return true;
        else if($this->carryOverValue == true && count($_POST) > 0 & !isset($_POST[$this->getName()]))
            return false;
        else if(($this->carryOverValue == true && count($_POST) == 0) || ($this->carryOverValue == false && count($_POST) == 0))
            return $this->toggled;
        else
            return $this->toggled;
    }
    
    function preflightGet(){
        if($this->getBool())
            $this->DOMElement->setChecked();
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldCheckboxGroupEntity extends formClassFieldCheckbox{
    function __construct($owner = null){
        parent::__construct();
        $this->owner = $owner;
    }
    
    function importFromFieldCheckbox($formFieldCheckbox){
        foreach($formFieldCheckbox as $key=>$value)
            $this->$key = $value;
    }
    
    function setOwner($owner){
        $this->owner = $owner;
        return $this;
    }
    
    function getOwner(){
        return $this->owner;
    }
    
    function escape(){
        return $this->owner;
    }
    
    function preflightGet(){
        if(in_array($this->getValue(),$this->getOwner()->getValue()))
            $this->DOMElement->setChecked();
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldCheckboxGroup extends formClassFieldTemplate{
        function __construct(){
            parent::__construct();
            $this->DOMElement = new _DOMElement('div');
            $this->value = array();
            $this->boxes = array();
        }
        function addItem(){
            $new = $this->boxes[] = new formClassFieldCheckboxGroupEntity($this);
            return $new;
        }
        function getValue(){
            if($this->carryOverValue && isset($_POST[$this->fieldName]))
                return $_POST[$this->fieldName];
            else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())
                return get_key(current($this->mySQLColumn->Table->getRows(true)),$this->mySQLColumn->Field);
            else
                return $this->value;
        }
        function setValue($value){
            function setValue($value){
                if(is_object($value) && get_class($value) == 'formClassFieldCheckboxGroupEntity')
                    $this->value[] = &$value->value;
                else $this->value[] = $value;
                return $this;
            }
        }
        function preflightGet(){
            foreach($this->boxes as $box){
                $box->setName(trim($this->getName(),'[]') . '[]');
                $this->DOMElement->addData($box->get());
            }
            return array($this->getDOMLabel(),$this->DOMElement->get());
        }
}

class formClassFieldRadio extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('input');
        $this->DOMElement->setType('radio');
        $this->carryOverValue = false;
    }
    
    function setBoolean($value){
        $this->toggled = $value;
        return $this;
    }
    
    function enableCarryOverValue(){
        return $this;
    }
    
    function getBool(){
        if($this->carryOverValue == true && count($_POST) > 0 && isset($_POST[$this->getName()]))
            return true;
        else if($this->carryOverValue == true && count($_POST) > 0 & !isset($_POST[$this->getName()]))
            return false;
        else if(($this->carryOverValue == true && count($_POST) == 0) || ($this->carryOverValue == false && count($_POST) == 0))
            return $this->toggled;
        else
            return $this->toggled;
    }
    
    function preflightGet(){
        if($this->getBool())
            $this->DOMElement->setChecked();
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldRadioGroupEntity extends formClassFieldRadio{
    function __construct($owner = null){
        parent::__construct();
        $this->owner = $owner;
    }
    
    function importFromFieldRadio($formFieldCheckbox){
        foreach($formFieldCheckbox as $key=>$value)
            $this->$key = $value;
    }
    
    function setOwner($owner){
        $this->owner = $owner;
        return $this;
    }
    
    function getOwner(){
        return $this->owner;
    }
    
    function escape(){
        return $this->owner;
    }
    
    function preflightGet(){
        if($this->getValue() == $this->getOwner()->getValue())
            $this->DOMElement->setChecked();
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->setName($this->getName());
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

class formClassFieldRadioGroup extends formClassFieldTemplate{
        function __construct(){
            parent::__construct();
            $this->DOMElement = new _DOMElement('div');
            $this->value = array();
            $this->boxes = array();
        }
        function addItem(){
            $new = $this->boxes[] = new formClassFieldRadioGroupEntity($this);
            return $new;
        }
        function getValue(){
            if($this->carryOverValue && isset($_POST[$this->fieldName]))
                return $_POST[$this->fieldName];
            else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())
                return get_key(current($this->mySQLColumn->Table->getRows(true)),$this->mySQLColumn->Field);
            else
                return $this->value;
        }
        function setValue($value){
            function setValue($value){
                if(is_object($value) && get_class($value) == 'formClassFieldRadioGroupEntity')
                    $this->value[] = &$value->value;
                else $this->value[] = $value;
                return $this;
            }
        }
        function preflightGet(){
            foreach($this->boxes as $box){
                $box->setName($this->getName());
                $this->DOMElement->addData($box->get());
            }
            return array($this->getDOMLabel(),$this->DOMElement->get());
        }
}

class formClassFieldSelectOption{
    function __construct($owner = null,$selectObject = null){
        $this->DOMElement = new _DOMElement('option');
        $this->value = '';
        $this->label = '';
        $this->owner = $owner;
        $this->selectObject = $selectObject;
    }
    
    function escape(){
        return $this->owner;
    }
    
    function __call($functionName, $args){
        if(preg_match('/^([gs]et_?)(.*)/', $functionName, $matches)){
            $return = call_user_func_array(array(&$this->DOMElement,$functionName),$args);
            if(rtrim($matches[1], '_') == 'set')return $this;
            else return $return;
        }
        else trigger_error("Unknown method: $functionName()");
    }
    
    function setValue($value){
        $this->value = $value;
        return $this;
    }
    
    function getValue(){
        return $this->value;
    }
    
    function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    function getLabel(){
        return $this->label;
    }
    
    function get(){
        $this->DOMElement->setValue($this->getValue());
        $this->DOMElement->addData($this->getLabel());
        if(in_array($this->getValue(),$this->selectObject->getValue()))$this->DOMElement->setSelected();
        return $this->DOMElement->get();
    }
}

class formClassFieldSelectGroup{
    function __construct($owner = null){
        $this->DOMElement = new _DOMElement('optgroup');
        $this->label = '';
        $this->options = array();
        $this->owner = $owner;
    }
    
    function escape(){
        return $this->owner;
    }
    
    function __call($functionName, $args){
        if(preg_match('/^([gs]et_?)(.*)/', $functionName, $matches)){
            $return = call_user_func_array(array(&$this->DOMElement,$functionName),$args);
            if(rtrim($matches[1], '_') == 'set')return $this;
            else return $return;
        }
        else trigger_error("Unknown method: $functionName()");
    }
    
    function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    function getLabel(){
        return $this->label;
    }
    
    function addOption(){
        $args = func_get_args();
        if (count($args) == 0){
            $option = &$this->options[count($this->options) - 1];
            $option = new formClassFieldSelectOption($this,$this->owner);
            return $option;
        }
        else if(count($args) == 1 && $option = $args[0] && (is_object($option) && get_class($option) == 'formClassFieldSelectOption')){
            if(!isset($option->owner))$option->owner = $this;
            if(!isset($option->selectObject))$option->selectObject = $this->owner;
            $this->options[] = $option;
        }
        else if(count($args) == 2 && (is_string($args[0]) && is_string($args[1]))){
            $temp = new formClassFieldSelectOption($this,$this->owner);
            $temp->setValue($args[1]);
            $temp->setLabel($args[0]);
            $this->options[] = $temp;
        }
        return $this;
    }
    
    function addOptions(){
        $args = func_get_args();
        foreach($args as $arg){
            if(is_array($arg))$this->addOptions($arg);
            else $this->addOption($arg);
        }
    }

    private function getOptionsOutput(){
        $output = '';
        foreach($this->options as $option)
            $output .= $option->get();
        return $output;
    }

    function get(){
        $this->DOMElement->setLabel($this->getLabel());
        $this->DOMElement->addData($this->getOptionsOutput());
        return $this->DOMElement->get();
    }
}

class formClassFieldSelect extends formClassFieldTemplate{
    function __construct(){
        parent::__construct();
        $this->DOMElement = new _DOMElement('select');
        $this->value = array();
        $this->options = array();
    }
    
    function setValue($value){
        if(is_object($value) && get_class($value) == 'formClassFieldSelectOption')
            $this->value[] = &$value->value;
        else $this->value[] = $value;
        return $this;
    }
    
    function getValue(){
        if($this->carryOverValue && isset($_POST[$this->fieldName]))
            return $_POST[$this->fieldName];
        else if(isset($this->mySQLColumn) && $this->mySQLColumn->Table->isSelection())
            return get_key(current($this->mySQLColumn->Table->getRows(true)),$this->mySQLColumn->Field);
        else
            return $this->value;
    }
    
    function addFromArray($array){
        foreach($array as $value => $option){
            if(is_array($option)){
                $group = $this->addGroup()->setLabel($value);
                foreach($option as $groupOptionValue => $groupOption)
                    $group->addOption()->setValue($groupOptionValue)->setLabel($groupOption);
            }
            else {
                $this->addOption()->setValue($value)->setLabel($option);
            }
        }
    }
    
    function addGroup($label = ''){
        $group = &$this->options[count($this->options) - 1];
        $group = new formClassFieldSelectGroup($this,$this);
        $group->setLabel($label);
        return $group;
    }
    
    function addOption(){
        $args = func_get_args();
        if (count($args) == 0){
            $option = &$this->options[count($this->options) - 1];
            $option = new formClassFieldSelectOption($this,$this);
            return $option;
        }
        else if(count($args) == 1 && (is_object($args[0]) && (get_class($args[0]) == 'formClassFieldSelectOption' || get_class($args[0]) == 'formClassFieldSelectGroup'))){
            $option = $args[0];
            if(!isset($option->owner))$option->owner = $this;
            if(!isset($option->selectObject))$option->selectObject = $this;
            $this->options[] = $option;
        }
        else if(count($args) == 2 && (is_string($args[0]) && is_string($args[1]))){
            $temp = new formClassFieldSelectOption($this,$this);
            $temp->setValue($args[1]);
            $temp->setLabel($args[0]);
            $this->options[] = $temp;
        }
        return $this;
    }

    function addOptions(){
        $args = func_get_args();
        foreach($args as $arg){
            if(is_array($arg))$this->addOptions($arg);
            else $this->addObject($arg);
        }
    }
    
    private function getOptionsOutput(){
        $output = '';
        foreach($this->options as $option){
            $output .= $option->get();
        }
        return $output;
    }
    
    function preflightGet(){
        $this->DOMElement->addData($this->getOptionsOutput());
        $this->DOMElement->setName(trim($this->getName(),'[]') . '[]');
        if(count($this->getValue()) > 1)$this->DOMElement->setMultiple();
        return array($this->getDOMLabel(),$this->DOMElement->get());
    }
}

$form = new formClass;
$form->getContainer()->setMethod('post')->setAction('./form.class.php');

$username = new formClassFieldText;
$username   ->setValue('Username')
            ->setLabel('Username')
            ->makeRequired();
        
$email = new formClassFieldText;
$email  ->setValue('Email Address')
        ->setLabel('Email')
        ->makeRequired()
        ->getValidationArguments()
            ->newValidationArgument()
                ->setFunction('isValidEmail')
                ->setMessage('Must be a valid email address.');

$password = new formClassFieldPassword;
$password   ->setValue('Password')
            ->setLabel('Password')
            ->setName('password')
            ->makeRequired();
            
$confirmPassword = new formClassFieldPassword;
$confirmPassword    ->setValue('Confirm Password')
                    ->setLabel('And again')
                    ->getValidationArguments()
                        ->newValidationArgument()
                            ->setFunction(function($array){if($array['field'] == $_POST['password'])return true; else return false;})
                            ->setMessage('Passwords must match!');

$select = new formClassFieldSelect;
$select->setLabel('Select');
$select->addOption()
            ->setLabel('Option One')
            ->setValue('1')
        ->escape()
        ->addOption()
            ->setLabel('Option Two')
            ->setValue('2')
        ->escape()
        ->addGroup('Group 1')
            ->addOption()
                ->setLabel('Group Option One')
                ->setValue('1')
                ->escape()
            ->addOption()
                ->setLabel('Group Option Two')
                ->setValue('2');

$radio = new formClassFieldRadioGroup;
$radio->
    setTag('div')->
    setLabel('Radio Buttons')->
    addItem()->
        setLabel('Radio One')->
        setValue('1')->
    escape()->
    addItem()->
        setLabel('Radio Two')->
        setValue('2')->
    escape()->
    setName('checkboxes')
    ->makeRequired();

$checkbox = new formClassFieldCheckboxGroup;
$checkbox->
    setTag('div')->
    setLabel('Checkboxes')->
    addItem()->
        setLabel('Box One')->
        setValue('1')->
    escape()->
    addItem()->
        setLabel('Box Two')->
        setValue('2')->
    escape()->
    makeRequired();
            
$submit = new formClassFieldSubmit;
$submit->setValue('Go!');

$other_container = new DOMContainerWithValidation('div');
$other_container->getValidationArguments()->newValidationArgument()->setFunction(function(){ echo "running"; return false;})->setMessage('WTF');
$other_container->addData($username, $email);

$container = new DOMContainerWithValidation('div');
$container->addData($other_container,$password,$confirmPassword);

$form->getContainer()->addData($container,$radio,$checkbox,$select,$submit);
echo $form->get();

/*
<form method="post" action="./form.class.php">
    <input type="hidden" value="60c0b530" name="formName"/>
    <div>
        <div>
            <label>Username</label>: <input type="text" value="Username" name="8a8faefa"/><br>
            <label>Email</label>: <input type="text" value="Email Address" name="1c404dea"/><br>
        </div>
        <label>Password</label>: <input type="password" value="Password" name="password"/><br>
        <label>And again</label>: <input type="password" value="Confirm Password" name="aa58c6ed"/><br>
    </div>
    <label>Radio Buttons</label>: 
    <div>
        <label>Radio One</label>: <input type="radio" value="1" name="checkboxes"/><br>
        <label>Radio Two</label>: <input type="radio" value="2" name="checkboxes"/><br>
    </div><br>
    <label>Checkboxes</label>: 
    <div>
        <label>Box One</label>: <input type="checkbox" value="1" name="baea9ab8[]"/><br>
        <label>Box Two</label>: <input type="checkbox" value="2" name="baea9ab8[]"/><br>
    </div><br>
    <label>Select</label>: <select name="0bc915f7[]">
        <option value="1">Option One</option>
        <option value="2">Option Two</option>
        <optgroup label="Group 1">
            <option value="1">Group Option One</option>
            <option value="2">Group Option Two</option>
        </optgroup>
    </select><br>
    <input type="submit" value="Go!" name="fd3f1cba"/>
</form>
*/
?>