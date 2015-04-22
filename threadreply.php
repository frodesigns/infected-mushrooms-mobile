<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	$threadtitle = "Thread Not Found";
	$threadid = $_GET['threadid'];
	
	$err = array();		

	if ($threadid) {
		$query = sprintf("SELECT * FROM threads WHERE threadid = $threadid");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0) {
			$threadid = null;
		} else {
			while ($row = mysql_fetch_assoc($result)) {
				$threadtitle = $row['title'];
				$private = $row['private'];
				
				//activity update
				$activity = mysql_real_escape_string("Replying to <a href=/im/thread.php?threadid=$threadid>$threadtitle</a> on Mobile");
				$sql = "UPDATE users SET lastactivity = '$activity', lastactivitytime = CURRENT_TIMESTAMP WHERE id = $currentuserid";
				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
				
				//online update
				$time = time();
				$updateonlinequery = "UPDATE online SET timeout = \"$time\" WHERE id = $currentuserid";
				if (!mysql_query($updateonlinequery,$link))
				{
					die('Error: ' . mysql_error());
				}
				
				if ($private == 1 && !isset($_SESSION['user_id'])) {
					header("Location: forum.php");
				}
				if ($private == 1) {
					$forumtype = "Private";
				} else {
					$forumtype = "Public";
				}
				
				if ($_POST['threadReply']=='Submit') {	
					$authorid = $_SESSION['user_id'];
					
					$content = stripslashes($_POST['content']);
					$content = stripslashes($content);
					$content = str_replace("'", "\'", $content);
					$content = strip_tags($content, $allowedTags);
					$content = nl2br($content);
					
					if (!$content) {
						$err[] = "You have to fill in all of the fields!";
					} else {
						$sql = "INSERT INTO posts (threadid, content, authorid, spam) VALUES ($threadid, '$content', $authorid, 0)";
						if (!mysql_query($sql,$link))
						{
							die('Error: ' . mysql_error());
						}			
						
						$sql = "UPDATE readthreads SET isread = 0 WHERE threadid = $threadid";
						if (!mysql_query($sql,$link))
						{
							die('Error: ' . mysql_error());
						}	
						
						$sql = "UPDATE threads SET timestamp = CURRENT_TIMESTAMP(), updatedbyid = $authorid, postcount = (postcount + 1) WHERE threadid = $threadid";
						if (!mysql_query($sql,$link))
						{
							die('Error: ' . mysql_error());
						}	
						
						header("Location: /thread.php?threadid=$threadid");
					}
				}
			}
		}
	}
	
	include 'base.php';
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
		<form action="/threadreply.php?threadid=<?php echo $threadid; ?>" method="post">
			<label for="content">Body:</label>
			<textarea name="content" id="content"><?php echo $content; ?></textarea>

			<div class="ui-grid-a">
				<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/thread.php?threadid=<?php echo $threadid; ?>">Cancel</a></div>
				<div class="ui-block-b"><input name="threadReply" type="submit" value="Submit" data-theme="b" /></div>
			</div>
		</form>
	<?php } ?>
<?php endblock() ?>