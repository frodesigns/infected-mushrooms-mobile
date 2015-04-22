<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	include 'Akismet.class.php';
	
	$err = array();
	
	$type = $_GET['type'];
	
	$pagetitle = "Create Thread";
	
	if ($type == "app") {
		$pagetitle = "Create Membership Application";
		$title = "Application: *your name here*";
		$content = "Your Real Name: 


Your Character(s): 


About Yourself: 


Your Helbreath History: 


Why do you think you would be a good fit for our guild?: 


Any comments you want to add?: ";
	}
	
	if(isset($_COOKIE['guestname'])) {
		$guestnamecookie = $_COOKIE['guestname']; 
	}
	if(isset($_COOKIE['guestemail'])) {
		$guestemailcookie = $_COOKIE['guestemail']; 
	}
	
	if ($_POST['createThread']=='Submit') {
		$authorid = "NULL";
		
		$guestname = sanitize($_POST['guestname']);
		$guestemail = sanitize($_POST['guestemail']);
		
		if ($guestname) {
			$inTwoMonths = 60 * 60 * 24 * 60 + time(); 
			setcookie('guestname', $guestname, $inTwoMonths);
			$guestnamecookie = $guestname;
		}
		
		if ($guestemail) {
			$inTwoMonths = 60 * 60 * 24 * 60 + time(); 
			setcookie('guestemail', $guestemail, $inTwoMonths);
			$guestemailcookie = $guestemail;
		}
		
		$query2 = sprintf("SELECT full_name FROM users WHERE full_name = '$guestname' OR user_name = '$guestname'");
		$result2 = mysql_query($query2);
		$num_rows = mysql_num_rows($result2);

		if ($num_rows != 0) {
			$err[] = "You can't pretend to be a guild member!  Please choose a different name.";
		} else {		
			$title = sanitize($_POST['title']);
			$content = stripslashes($_POST['content']);
			$content = stripslashes($content);
			$content = str_replace("'", "\'", $content);
			$content = strip_tags($content, $allowedTags);
			$content = nl2br($content);
			
			if (!$title || !$content || !$guestname || !$guestemail) {
				$err[] = "You have to fill in all of the fields!";
			} else {
			
				$akismet = new Akismet($MyURL ,$AkismetAPIKey);					
				$akismet->setCommentAuthor($guestname);
				$akismet->setCommentAuthorEmail($guestemail);
				$akismet->setCommentContent($content);
				
				if($akismet->isCommentSpam()) {
					$err[] = "Spam Detected!";
				} else {				
					$sql = "INSERT INTO threads (title, private, authorid, updatedbyid) VALUES ('$title', 0, 0, 0)";
					if (!mysql_query($sql,$link))
					{
						die('Error: ' . mysql_error());
					}
					
					$query = sprintf("SELECT threadid FROM threads WHERE private = 0 ORDER BY timestamp DESC LIMIT 1");
					$result = mysql_query($query);
					while ($row = mysql_fetch_assoc($result)) {
						$threadid = $row['threadid'];
					}
					
					$sql = "INSERT INTO posts (threadid, content, authorid, guestname, guestemail) VALUES ($threadid, '$content', $authorid, '$guestname', '$guestemail')";

					if (!mysql_query($sql,$link))
					{
						die('Error: ' . mysql_error());
					}
					
					$query2 = sprintf("SELECT id FROM users");
					$result2 = mysql_query($query2);
					while ($row2 = mysql_fetch_assoc($result2)) {
						$userid = $row2['id'];
						
						$sql = "INSERT INTO readthreads (id, threadid, isread) VALUES ($userid, $threadid, 0)";
						if (!mysql_query($sql,$link))
						{
							die('Error: ' . mysql_error());
						}
					}
					
					header("Location: /public/thread-public.php?threadid=$threadid");				
				}
				
			}
		}
	}
	
	include 'base-public.php';	
?>

<?php startblock('title') ?>
	<?php echo $pagetitle; ?>
<?php endblock() ?>

<?php startblock('header') ?>
	<?php echo $pagetitle; ?>
<?php endblock() ?>

<?php startblock('content') ?>
	<h2><?php echo $pagetitle; ?></h2>
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
	<form action="/public/newthread-public.php" method="post">
		<fieldset data-role="controlgroup">
			<label for="guestname">Guest Name:</label>
			<input type="text" name="guestname" id="guestname" maxlength="14" value="<?php echo $guestnamecookie; ?>">
			
			<label for="guestemail">Guest Email: <small>This will not be published!</small></label>
			<input type="text" name="guestemail" maxlength="50" id="guestemail" value="<?php echo $guestemailcookie; ?>">
			
			<label for="title">Subject:</label>
			<input type="text" name="title" id="title" value="<?php echo $title; ?>" />
			
			<label for="content">Body:</label>
			<textarea name="content" id="content"><?php echo $content; ?></textarea>
		</fieldset>
		
		<div class="ui-grid-a">
			<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/public/pubforum-public.php">Cancel</a></div>
			<div class="ui-block-b"><input name="createThread" type="submit" value="Submit" data-theme="b" /></div>
		</div>
	</form>
<?php endblock() ?>