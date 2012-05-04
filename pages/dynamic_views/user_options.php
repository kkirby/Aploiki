<?php
//This is using the old form class.
$page_css = '
    input {margin-left:10px; width:200px;}
	select {margin-left:10px; width:200px;}
	label {
		float: left;
		text-align: right;
		width: 110px;
		padding-top: 2px;
		weight: bold;
	}
	.submit_button {margin-left: 0px; width: auto;}
    .required {border: solid 1px red;}
';


// Change Password //
$page_text = "<h2>Change Password</h2>";
$changePasswordForm = new form_creator;
$changePasswordForm->setName('password');
$changePasswordFormFields = $changePasswordForm->explodeClasses();
$passwordField = $changePasswordFormFields['password']->createNew()
                ->setAttributes(array('name'=>'password'))
                ->setLabel('Password')
                ->makeRequired('A new password is required in order to change your old one.')
                ->disableCarryOver();
                
$passwordFieldConfirm = $changePasswordFormFields['password']->createNew()
                ->setAttributes(array('name'=>'passwordConfirm'))
                ->setLabel('Password Confirm')
                ->makeRequired('You must confirm your password!')
                ->addExpressionCheck(array(
                    'expression'=>
                        'if($_POST[\'password\'] == $_POST[\'passwordConfirm\'])return true;else return false;',
                    'message'=>'Your passwords do not match.'
                    ))
                ->disableCarryOver();
$submitButton = $changePasswordFormFields['submit']->createNew()
                ->setValue('Change Password.');
                    
$changePasswordForm   ->addFields(array($passwordField, $passwordFieldConfirm,$submitButton));

$passwordisOkay = $changePasswordFormFields['validator']->setFields($changePasswordForm)
                ->applyArgumentsToRequiredFields(array('class'=>'required','onfocus'=>'this.removeClassName(\'required\');'))->checkForm();
                
$page_text .= $changePasswordForm->printForm();
if ($passwordisOkay){
    if($_AEYNIAS['class']['mysql']->update_row('users',array('password' => md5($_POST['password'])),array('id'=>$_SESSION['user_id']))){
        praise('Your password has been updated successfully!');
        $_SESSION['password'] = md5($_POST['pass']);
    }
    else error('Something went wrong, sorry ...');
}

// Change username //
$page_text .= "<h2>Change Username</h2>";
$changeUsernameForm = new form_creator;
$changeUsernameForm->setName('username');
$changeUsernameFormFields = $changeUsernameForm->explodeClasses();
$usernameField = $changeUsernameFormFields['generic']->createNew()
                ->setLabel('Username')
                ->setName('username')
                ->setValue($_SESSION['username'])
                ->makeRequired('You have to give us a new user name so we can update your information!')
                ->addExpressionCheck(array(
                    'expression'=>
                        'if($_SESSION[\'username\'] == $_POST[\'username\'])return false;else return true;',
                    'message'=>'The username you supplied is exactly the same as your current username. Try being a bit more creative?'
                    ))
                ->addExpressionCheck(array(
                    'expression'=>
                        'if($_AEYNIAS[\'class\'][\'mysql\']->validate(\'users\',array(\'username\'=>$_POST[\'username\'])))return false;else return true;',
                    'message'=>'The user name you want is currently in use.'
                    ));
$changeUsernameSubmitButton = $changeUsernameFormFields['submit']->createNew()->setValue('Change Username.');
$changeUsernameForm->addFields(array($usernameField,$changeUsernameSubmitButton));
$usernameisOkay = $changeUsernameFormFields['validator']->setFields($changeUsernameForm)
                ->applyArgumentsToRequiredFields(array('class'=>'required','onfocus'=>'this.removeClassName(\'required\');'))
                ->checkForm();
$page_text .= $changeUsernameForm->printForm();
if($usernameisOkay){
    if ($_AEYNIAS['class']['mysql']->update_row('users',array('username'=>$_POST['username']),array('id'=>$_SESSION['user_id']))){
        praise('Your username has been updated successfully!');
        $_SESSION['username'] = $_POST['username'];
    }
    else error('Something went wrong, sorry ...');
}

// Change Theme //
$page_text .= "<h2>Change Theme</h2>";
$changeThemeForm = new form_creator;
$changeThemeForm->setName('theme');
$changeThemeFormFields = $changeThemeForm->explodeClasses();
$themeField = $changeThemeFormFields['select']->createNew()
                ->setLabel('Theme')
                ->setName('theme')
                ->setValue($_AEYNIAS['authentication']['user']['theme'])
                ->setOptions($_AEYNIAS['class']['theme']->grab_all_themes())
                ->makeRequired('It would be nice if you actually selected a theme before going all trigger happy on that submit button.')
                ->addExpressionCheck(array(
                    'expression'=>
                        'if($_AEYNIAS[\'class\'][\'theme\']->check_if_theme_exists($_POST[\'theme\']))return true;else return false;',
                    'message'=>'I give you some serious props, you have selected a theme that does not exist.'
                    ));

$changeThemeSubmitButton = $changeThemeFormFields['submit']->createNew()->setValue('Change Theme.');
$changeThemeForm->addFields(array($themeField,$changeThemeSubmitButton));
$themeisOkay = $changeThemeFormFields['validator']->setFields($changeThemeForm)
                ->applyArgumentsToRequiredFields(array('class'=>'required','onfocus'=>'this.removeClassName(\'required\');'))
                ->checkForm();
$page_text .= $changeThemeForm->printForm();
if($themeisOkay){
    if ($_AEYNIAS['class']['mysql']->update_row('users',array('theme'=>$_POST['theme']),array('id'=>$_SESSION['user_id']))){
        praise('Your theme has been updated successfully!');
        $_AEYNIAS['authentication']['user']['theme'] = $_POST['theme'];
    }
    else error('Something went wrong, sorry ...');
}
?>