<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Creatng a Forum Thread on Mobile");
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
	
	$err = array();
	
	if ($_POST['createThread']=='Submit') {
		$authorid = $_SESSION['user_id'];
		
		if(isset($_POST['sticky']) && $_POST['sticky'] == '1') {
			$sticky = 1;
			$stickychecked = "checked='checked'";
		} else {
			$sticky = 0;
			$stickychecked = "";
		}	
		
		if(isset($_POST['public']) && $_POST['public'] == '1') {
			$private = 0;
			$publicchecked = "checked='checked'";
		} else {
			$private = 1;
			$publicchecked = "";
		}
		
		$title = sanitize($_POST['title']);
		$content = stripslashes($_POST['content']);
		$content = stripslashes($content);
		$content = str_replace("'", "\'", $content);
		$content = strip_tags($content, $allowedTags);
		$content = nl2br($content);
		
		if (!$title || !$content) {
			$err[] = "You have to fill in all of the fields!";
		} else {
			$sql = "INSERT INTO threads (title, private, sticky, authorid, updatedbyid) VALUES ('$title', $private, $sticky, $authorid, $authorid)";
			if (!mysql_query($sql,$link))
			{
				die('Error: ' . mysql_error());
			}
			
			$query = sprintf("SELECT threadid FROM threads ORDER BY timestamp DESC LIMIT 1");
			$result = mysql_query($query);
			while ($row = mysql_fetch_assoc($result)) {
				$threadid = $row['threadid'];
			}
			
			$sql = "INSERT INTO posts (threadid, content, authorid) VALUES ($threadid, '$content', $authorid)";

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
			
			header("Location: /thread.php?threadid=$threadid");
		}
	}
	
	include 'base.php';	
?>

<?php startblock('title') ?>
	Create Thread
<?php endblock() ?>

<?php startblock('header') ?>
	Create Thread
<?php endblock() ?>

<?php startblock('content') ?>
	<h2>Create Thread</h2>
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
	<form action="/newthread.php" method="post">
		<fieldset data-role="controlgroup">
			<label for="title">Subject:</label>
			<input type="text" name="title" id="title" value="<?php echo $title; ?>" />
			
			<label for="content">Body:</label>
			<textarea name="content" id="content"><?php echo $content; ?></textarea>
		</fieldset>
		<fieldset data-role="controlgroup">
			<input type="checkbox" name="public" id="public" value="1" <?php echo $publicchecked; ?> />
			<label for="public">Make Public?</label>
			
			<?php if (checkAdmin()) { ?>
				<input type="checkbox" name="sticky" id="sticky" value="1" <?php echo $stickychecked; ?> />
				<label for="sticky">Sticky? (Admin Only)</label>
			<?php } ?>
		</fieldset>
		
		<div class="ui-grid-a">
			<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/forum.php">Cancel</a></div>
			<div class="ui-block-b"><input name="createThread" type="submit" value="Submit" data-theme="b" /></div>
		</div>
	</form>
<?php endblock() ?>