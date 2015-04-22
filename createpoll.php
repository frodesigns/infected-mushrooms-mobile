<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Creating a Poll on Mobile");
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
	
	if ($_POST['createPoll']=='Submit') {

		if(isset($_POST['public']) && $_POST['public'] == '1') {
			$public = 1;
			$publicchecked = "checked='checked'";
		} else {
			$public = 0;
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
			$sql = "INSERT INTO polls (title, description, isrecruit, createdbyid) VALUES ('$title', '$content', $public, $currentuserid)";
			if (!mysql_query($sql,$link))
			{
				die('Error: ' . mysql_error());
			}	
			
			header("Location: /polls.php");
		}
	}
	
	include 'base.php';	
?>

<?php startblock('title') ?>
	Create Poll
<?php endblock() ?>

<?php startblock('header') ?>
	Create Poll
<?php endblock() ?>

<?php startblock('content') ?>
	<h2>Create Poll</h2>
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
	<form action="/createpoll.php" method="post">
		<fieldset data-role="controlgroup">
			<label for="title">Title:</label>
			<input type="text" name="title" id="title" value="<?php echo $title; ?>" />
			
			<label for="content">Info:</label>
			<textarea name="content" id="content"><?php echo $content; ?></textarea>
		</fieldset>
		<fieldset data-role="controlgroup">
			<input type="checkbox" name="public" id="public" value="1" <?php echo $publicchecked; ?> />
			<label for="public">New Recruit? (Public Poll)</label>
		</fieldset>
		
		<div class="ui-grid-a">
			<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/polls.php">Cancel</a></div>
			<div class="ui-block-b"><input name="createPoll" type="submit" value="Submit" data-theme="b" /></div>
		</div>
	</form>
<?php endblock() ?>