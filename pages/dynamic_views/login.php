<?php
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
';
if ($_AEYNIAS['authentication']['user']['logged_in'] == false){
    if(isset($_SESSION['login_redir']) && ($_SESSION['login_redir'][1] > time() || isset($_POST['username'])))error('You must be logged in to visit this page.');  // basic time verification. After three minutes it will unset the ReDir session, and the next time a user logsin, it wont redirect them to some obscure page....
    else if (!isset($_POST['username']))unset($_SESSION['login_redir']);
	$form = <<<END
	<form method="post" action="{$_AEYNIAS['config']['doc_url']}/login/">
		<label>Username:</label> <input type="text" name="username"><br>
		<label>Password:</label> <input type="password" name="password"><br>
	<input type="submit" value="Login." class="submit_button">
	</form>
END;
	if (isset($_POST['username'])){
		$array = array('username' => $_POST['username'], 'password' => md5($_POST['password']));
		if ($user_info = $_AEYNIAS['class']['mysql']->quick_grab('users','*',$array)){
			/////////////
			$_SESSION['user_id'] = $user_info['id'];
			$_SESSION['username'] = $user_info['username'];
			$_SESSION['password'] = $user_info['password'];
			$_AEYNIAS['authentication']['user']['logged_in'] = true;
    		if ($user_info['admin'] == 1)$_AEYNIAS['authentication']['admin']['logged_in'] = true;
    		else $_AEYNIAS['authentication']['admin']['logged_in'] = false;
    		$_AEYNIAS['authentication']['user']['username'] = $user_info['username'];
    		$_AEYNIAS['authentication']['user']['password'] = $user_info['password'];
    		$_AEYNIAS['authentication']['user']['theme'] = $user_info['theme'];
    		////////////
			
			praise("You have been successfully logged in!");
			$page_text = '<a href="'.$_AEYNIAS['config']['doc_url'].'">Back home?</a>';
			if (isset($_SESSION['login_redir'])){
				header('LOCATION: ' . $_SESSION['login_redir'][0]);
				unset($_SESSION['login_redir']);
			}
		}
		else {
			error('It appears that the username and/or password you supplied is invalid.');
			$page_text = $form;
		}
	}
	else {
		$page_text = $form;
	}
}
else {
	error('You are already logged in!');
	$page_text = '';
}
?>