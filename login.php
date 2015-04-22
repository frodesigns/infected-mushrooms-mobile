<?php
	session_start();
	include 'dbc.php';
	include 'currentuser.php';
	
	if (isset($_SESSION['user_id'])) {
		header("Location: /index.php");
	}
	
	$err = array();

	foreach($_GET as $key => $value) {
		$get[$key] = filter($value); //get variables are filtered.
	}

	if ($_POST['doLogin']=='Login') {

		foreach($_POST as $key => $value) {
			$data[$key] = filter($value); // post variables are filtered
		}

		$user_email = $data['username'];
		$pass = $data['password'];

		if (strpos($user_email,'@') === false) {
			$user_cond = "user_name='$user_email'";
		} else {
			$user_cond = "user_email='$user_email'";
		}

		$result = mysql_query("SELECT `id`,`pwd`,`full_name`,`approved`,`user_level` FROM users WHERE $user_cond AND `banned` = '0'") or die (mysql_error()); 
		$num = mysql_num_rows($result);

		// Match row found with more than 1 results  - the user is authenticated. 
		if ( $num > 0 ) { 
		
			list($id,$pwd,$full_name,$approved,$user_level) = mysql_fetch_row($result);
		
			if(!$approved) {
				//$msg = urlencode("Account not activated. Please check your email for activation code");
				$err[] = "Account not activated. Please check your email for activation code";
		
				//header("Location: login.php?msg=$msg");
				//exit();
			}
		 
			//check against salt
			if ($pwd === PwdHash($pass,substr($pwd,0,9))) { 
			
				if(empty($err)){			

					// this sets session and logs user in  
					session_start();
				    session_regenerate_id (true); //prevent against session fixation attacks.

				    // this sets variables in the session 
					$_SESSION['user_id']= $id;  
					$_SESSION['user_name'] = $full_name;
					$_SESSION['user_level'] = $user_level;
					$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
			
					//update the timestamp and key for cookie
					$stamp = time();
					$ckey = GenKey();
					mysql_query("update users set `ctime`='$stamp', `ckey` = '$ckey' where id='$id'") or die(mysql_error());
			
					//set a cookie 
					if(isset($_POST['remember'])){
						setcookie("user_id", $_SESSION['user_id'], time()+60*60*24*COOKIE_TIME_OUT, "/");
					    setcookie("user_key", sha1($ckey), time()+60*60*24*COOKIE_TIME_OUT, "/");
					    setcookie("user_name",$_SESSION['user_name'], time()+60*60*24*COOKIE_TIME_OUT, "/");
					}
					
					header("Location: /index.php");
				}
			} else {
				//$msg = urlencode("Invalid Login. Please try again with correct user email and password. ");
				$err[] = "Invalid Login. Please try again with correct user email and password.";
				//header("Location: login.php?msg=$msg");
			}
		} else {
			$err[] = "Error - Invalid login. No such user exists";
		}		
	}
	
	include 'public/base-public.php';
?>

<?php startblock('title') ?>
	Login
<?php endblock() ?>

<?php startblock('header') ?>
	Login
<?php endblock() ?>

<?php startblock('content') ?>
	<h2>Login</h2>
	<?php
		/******************** ERROR MESSAGES*************************************************
		This code is to show error messages 
		**************************************************************************/
		if(!empty($err))  {
			echo "<p class=\"errors\">";
			foreach ($err as $e) {
				echo "$e <br>";
			}
			echo "</p>";	
		}
		/******************************* END ********************************/	  
	?>
	<form action="/login.php" method="post" data-ajax="false">
		<div data-role="fieldcontain" class="ui-hide-label">
			<label for="username">Username:</label>
			<input type="text" name="username" id="username" value="" placeholder="Username" />
		</div>
		<div data-role="fieldcontain" class="ui-hide-label">
			<label for="password">Password:</label>
			<input type="password" name="password" id="password" value="" placeholder="Password" />
		</div>
		<input type="checkbox" name="remember" id="remember" value="1" />
		<label for="remember">Remember Me</label>
		<input name="doLogin" type="submit" value='Login' data-inline='true' data-theme='b' />
	</form>
<?php endblock() ?>