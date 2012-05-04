<?php
// This is using the old form class.
$page_css = '
	input {margin-left:10px; width:200px;}
	label {
		float: left;
		text-align: right;
		width: 110px;
		padding-top: 2px;
		weight: bold;
	}
	.submit_button {margin-left: 0px; width: auto;}
	.required {
	    border: solid 1px red;
        background-color: #FCF5E9;
	}
';

$formCreator = new form_creator();
$formFields = $formCreator->explodeClasses();

$username = $formFields['generic']->
            createNew()->
            setName('username')->
            setLabel('Username')->
            setName('username')->
            makeRequired('You must supply a username!')->
            addExpressionCheck(array(
            'expression'=>
                'if($_AEYNIAS[\'class\'][\'mysql\']->validate(\'users\',array(\'username\'=>$_POST[\'username\'])))return false; else return true;',
            'message'=>'The user name you have supplied is already taken. Please pick another one.'));

$password = $formFields['password']->
            createNew()->
            setName('password')->
            setLabel('Password')->
            disableCarryOver()->
            makeRequired('You must supply a password!');
            
$passwordConfirm = $formFields['password']->
    createNew()->
    setName('confirmPassword')->
    setLabel('Confirm Password')->
    disableCarryOver()->
    addExpressionCheck(array(
    'expression'=>
        'if($_POST[\'password\'] != \'\' && $_POST[\'password\'] == $_POST[\'confirmPassword\'])return true; else return false;',
    'message' => 'Your passwords must match!'));
    
$submit = $formFields['submit']->createNew()->setValue('Create a new user!');

$formCreator->addFields(array($username,$password,$passwordConfirm,$submit));
if ($formFields['validator']->setFields($formCreator->getFields())->applyArgumentsToRequiredFields(array('class'=>'required','onfocus'=>'this.removeClassName(\'required\');'))->checkForm('Your account has been succesfully created!')){
    if (!$_AEYNIAS['class']['mysql']->insert_row('users',array('username'=>$_POST['username'], 'password' => md5($_POST['password']))))error('Something went wrong, sorry..');
}
else $page_text = $formCreator->printForm();

/*

$form = new make_form('./');
$form->insert_input('Username:','username','',1);
$form->insert_password('Password:','pass',1);
$form->insert_password('Confirm Password:','pass2',1,'You must confirm your password.');
$form->insert_submit('Go'); 

$post_form->set_keys($form->get_keys());
if (isset($_POST['submit'])){
	if ($post_form->check_form()){
		// continue
		if ($_AEYNIAS['class']['mysql']->validate('users',array('username'=>$_POST['username']))){
			error('That username is already taken :(');
			$page_text = $form->return_form();
		}
		else {
			if ($_POST['pass'] == $_POST['pass2']){
				if ($_AEYNIAS['class']['mysql']->insert_row('users',array('username'=>$_POST['username'], 'password' => md5($_POST['pass']))))praise('Your account has been made succesfully!');
				else error('Something went wrong, sorry..');
				$page_text = '';
			}
			else {
				error('Your passwords don\'t match, try again.');
				$page_text = $form->return_form();
				
			}
		}
	}
	else $page_text = $form->return_form();
}
else $page_text = $form->return_form();
*/
?>