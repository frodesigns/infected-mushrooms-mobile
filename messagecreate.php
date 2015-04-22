<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Writing a Message on Mobile");
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
	
	$query3 = sprintf("SELECT full_name, id FROM users WHERE approved = 1 AND banned = 0 ORDER BY full_name ASC");
	$result3 = mysql_query($query3);
	
	if ($_POST['createMessage']=='Submit') {
				
		$fromid = $currentuserid;
		$toid = $_POST['item'];		
		$replytomessageid = 0;
		
		$title = sanitize($_POST['title']);
		$content = stripslashes($_POST['content']);
		$content = stripslashes($content);
		$content = str_replace("'", "\'", $content);
		$content = strip_tags($content, $allowedTags);
		$content = nl2br($content);
		
		if (!$title || !$content || !$toid) {
			$err[] = "You have to fill in all of the fields!";
		} else {		
			if ($toid == "admins") {
				$query = sprintf("SELECT id FROM users WHERE user_level = 5 AND id != $fromid");
				$result = mysql_query($query);
				while ($row = mysql_fetch_assoc($result)) {
					$adminid = $row['id'];
					$sql = "INSERT INTO messages (replytomessageid, toid, fromid, title, content) VALUES ($replytomessageid, $adminid, $fromid, '$title', '$content')";
					if (!mysql_query($sql,$link))
					{
						die('Error: ' . mysql_error());
					}		
				}
			} else {
				$sql = "INSERT INTO messages (replytomessageid, toid, fromid, title, content) VALUES ($replytomessageid, $toid, $fromid, '$title', '$content')";
				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}		
			}	
			
			$query2 = sprintf("SELECT messageid FROM messages WHERE fromid = $currentuserid ORDER BY timestamp DESC LIMIT 1");
			$result2 = mysql_query($query2);
			while ($row2 = mysql_fetch_assoc($result2)) {
				$newmessageid = $row2['messageid'];
			}
			
			header("Location: /message.php?messageid=$newmessageid");
		}
	}

	include 'base.php';
?>

<?php startblock('title') ?>
	New Private Message
<?php endblock() ?>

<?php startblock('header') ?>
	New Private Message
<?php endblock() ?>

<?php startblock('content') ?>
	<h2>New Private Message</h2>
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
	<form action="/messagecreate.php" method="post">
		<?php
			echo "Send To: <select name='item' class='passedto'>";
			echo "<option value=''>Select a Name</option>";
			if (checkAdmin()) {
				echo "<option value='admins'>All Admins</option>";
			}				
			while ($row3 = mysql_fetch_assoc($result3)) {
				$id = $row3['id'];
				$value = $id;	
				if ($value == $toid){
					echo "<option value='$value' selected>", $row3['full_name'] ,"</option>";
				} else {
					echo "<option value='$value'>", $row3['full_name'] ,"</option>";
				}				
			}
			echo "</select> ";
		?>
				
		<label for="title">Subject:</label>
		<input type="text" name="title" id="title" value="<?php echo $title; ?>" />
		
		<label for="content">Body:</label>
		<textarea name="content" id="content"><?php echo $content; ?></textarea>
		
		<div class="ui-grid-a">
			<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/messages.php">Cancel</a></div>
			<div class="ui-block-b"><input name="createMessage" type="submit" value="Submit" data-theme="b" /></div>
		</div>
	</form>
<?php endblock() ?>