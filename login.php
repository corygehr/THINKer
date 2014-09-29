<?php
session_start();
session_destroy();
session_start();

	if(isset($_POST['password']))
	{
		if($_POST['password'] == 'TooManyHeartsOneHome2015')
		{
			// Set session
			$_SESSION['AUTH_ID'] = $_POST['password'];
			// Redirect
			header('Location: index.php');
			exit();
		}
	}
?>
<html>
	<head>
		<title>THINKer Temporary Login</title>
	</head>
	<body>
		<h1>THINKer Login</h1>
		<p>
			Please enter the password to login to THINKer.
		</p>
		<form id='login' method='post' action='login.php'>
			<fieldset>
				<legend>Credentials</legend>
				<input type='hidden' id='phase' name='phase' value='login' />
				<p>
					<input type='password' id='password' name='password' />
				</p>
				<input type='submit' value='Login' />
			</fieldset>
		</form>
	</body>
</html>