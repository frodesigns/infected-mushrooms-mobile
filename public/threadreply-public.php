<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	include 'Akismet.class.php';
	
	$threadtitle = "Thread Not Found";
	$threadid = $_GET['threadid'];
	
	$err = array();		
	
	if(isset($_COOKIE['guestname'])) {
		$guestnamecookie = $_COOKIE['guestname']; 
	}
	if(isset($_COOKIE['guestemail'])) {
		$guestemailcookie = $_COOKIE['guestemail']; 
	}

	if ($threadid) {
		$query = sprintf("SELECT * FROM threads WHERE threadid = $threadid AND private = 0");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0) {
			$threadid = null;
		} else {
			while ($row = mysql_fetch_assoc($result)) {
				$threadtitle = $row['title'];
				
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
				
					if ($_POST['threadReply']=='Submit') {	
						$authorid = "NULL";
						
						$content = stripslashes($_POST['content']);
						$content = stripslashes($content);
						$content = str_replace("'", "\'", $content);
						$content = strip_tags($content, $allowedTags);
						$content = nl2br($content);
						
						if (!$content || !$guestname || !$guestemail) {
							$err[] = "You have to fill in all of the fields!";
						} else {
							$akismet = new Akismet($MyURL ,$AkismetAPIKey);					
							$akismet->setCommentAuthor($guestname);
							$akismet->setCommentAuthorEmail($guestemail);
							$akismet->setCommentContent($content);
							
							if($akismet->isCommentSpam()) {
								$err[] = "Spam Detected!";
							} else {				
							
								$sql = "INSERT INTO posts (threadid, content, guestname, guestemail, authorid, spam) VALUES ($threadid, '$content', '$guestname', '$guestemail', 0, 0)";
								if (!mysql_query($sql,$link))
								{
									die('Error: ' . mysql_error());
								}			
								
								$sql = "UPDATE threads SET timestamp = CURRENT_TIMESTAMP(), updatedbyid = 0, postcount = (postcount + 1) WHERE threadid = $threadid";
								if (!mysql_query($sql,$link))
								{
									die('Error: ' . mysql_error());
								}	
								
								header("Location: /public/thread-public.php?threadid=$threadid");
							
							}
						}
					}
				
				}
			}
		}
	}
	
	include 'base-public.php';
?>

<?php startblock('title') ?>
	<?php if (!$threadid) { ?>
		<?php echo $threadtitle; ?> - <?php echo $forumtype; ?> Forum
	<?php } else { ?>
		Reply to <?php echo $threadtitle; ?> - <?php echo $forumtype; ?> Forum
	<?php } ?>
<?php endblock() ?>

<?php startblock('header') ?>
	<?php if (!$threadid) { ?>
		<?php echo $threadtitle; ?> - <?php echo $forumtype; ?> Forum
	<?php } else { ?>
		Reply to <em><?php echo $threadtitle; ?></em> - <?php echo $forumtype; ?> Forum
	<?php } ?>
<?php endblock() ?>

<?php startblock('content') ?>
	<?php if (!$threadid) { ?>
		<h2><?php echo $threadtitle; ?></h2>
		<p>
			<a data-inline="true" data-rel="back" data-mini="true" data-theme="a" data-role="button" href="/forum.php">Go Back</a>
		</p>
	<?php } else { ?>
		<h2>Reply to <em><?php echo $threadtitle; ?></h2></em>
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
		<form action="/public/threadreply-public.php?threadid=<?php echo $threadid; ?>" method="post">
			<label for="guestname">Guest Name:</label>
			<input type="text" name="guestname" id="guestname" maxlength="14" value="<?php echo $guestnamecookie; ?>">
			
			<label for="guestemail">Guest Email: <small>This will not be published!</small></label>
			<input type="text" name="guestemail" maxlength="50" id="guestemail" value="<?php echo $guestemailcookie; ?>">
			
			<label for="content">Body:</label>
			<textarea name="content" id="content"><?php echo $content; ?></textarea>

			<div class="ui-grid-a">
				<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/public/thread-public.php?threadid=<?php echo $threadid; ?>">Cancel</a></div>
				<div class="ui-block-b"><input name="threadReply" type="submit" value="Submit" data-theme="b" /></div>
			</div>
		</form>
	<?php } ?>
<?php endblock() ?>