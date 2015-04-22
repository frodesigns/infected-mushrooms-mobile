<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	$messagetitle = "Message Not Found";
	$messageid = $_GET['messageid'];
	
	if ($messageid) {
	
		$query = sprintf("SELECT a.*, b.full_name AS 'sender', c.full_name AS 'receiver'
			FROM messages a
			INNER JOIN users b ON b.id = a.fromid
			INNER JOIN users c ON c.id = a.toid
			WHERE messageid = $messageid");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0) {
			$messageid = null;
		} else {		
			while ($row = mysql_fetch_assoc($result)) {
				//activity update
				$activity = mysql_real_escape_string("Replying to a Message on Mobile");
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
			
				$messageid = (int)$row['messageid'];
				$messagetitle = $row['title'];
				$origcontent = "<blockquote>" . $row['content'] . "</blockquote>";
				$isread = $row['isread']; 
				$timestamp = date("F j, Y - g:i a", strtotime($row['timestamp']));
				$replytomessageid = $row['replytomessageid'];
				$sender = $row['sender'];
				$receiver = $row['receiver'];
				$toid = $row['toid'];
				$fromid = $row['fromid'];
				
				if (!$replytomessageid) {
					$replytomessageid = 0;
				}
				
				if ($_POST['messageReply']=='Submit') {	
					$authorid = $_SESSION['user_id'];
					
					$content = stripslashes($_POST['content']);
					$content = stripslashes($content);
					$content = str_replace("'", "\'", $content);
					$content = strip_tags($content, $allowedTags);
					$content = nl2br($content);
					
					if (substr($messagetitle, 0, 3) != 'RE:') {
						$messagetitle = "RE: " . $messagetitle;
					}
					
					if (!$content) {
						$err[] = "You have to fill in all of the fields!";
					} else {
						$sql = "INSERT INTO messages (replytomessageid, toid, fromid, title, content) VALUES ($replytomessageid, $fromid, $authorid, '$messagetitle', '$content')";
						if (!mysql_query($sql,$link))
						{
							die('Error: ' . mysql_error());
						}
						
						$query2 = sprintf("SELECT messageid FROM messages WHERE fromid = $authorid ORDER BY timestamp DESC LIMIT 1");
						$result2 = mysql_query($query2);
						while ($row2 = mysql_fetch_assoc($result2)) {
							$newmessageid = $row2['messageid'];
						}
						
						header("Location: /message.php?messageid=$newmessageid");
					}
				}
			}
		}
	
	}
	
	include 'base.php';
?>

<?php startblock('title') ?>	
	<?php if (!$messageid) { ?>
		<?php echo $messagetitle; ?> - Messages
	<?php } else { ?>
		Reply to <?php echo $messagetitle; ?> - Messages
	<?php } ?>
<?php endblock() ?>

<?php startblock('header') ?>
	<?php if (!$messageid) { ?>
		<?php echo $messagetitle; ?> - Messages
	<?php } else { ?>
		Reply to <em><?php echo $messagetitle; ?></em> - Messages
	<?php } ?>
<?php endblock() ?>

<?php startblock('content') ?>
	<?php if (!$messageid) { ?>
		<h2><?php echo $messagetitle; ?></h2>
		<p>
			<a data-inline="true" data-rel="back" data-mini="true" data-theme="a" data-role="button" href="/messages.php">Go Back</a>
		</p>
	<?php } else { ?>
		<h2>Reply to <em><?php echo $messagetitle; ?></em></h2>
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
		<form action="/messagereply.php?messageid=<?php echo $messageid; ?>" method="post">			
			<p>
				Replying To <em><?php echo $sender; ?>'s</em> Message:
			</p>
			<p>
				<?php echo $origcontent; ?>
			</p>
			
			<label for="content">Body:</label>
			<textarea name="content" id="content"></textarea>

			<div class="ui-grid-a">
				<div class="ui-block-a"><a data-rel="back" data-theme="a" data-role="button" href="/message.php?messageid=<?php echo $messageid; ?>">Cancel</a></div>
				<div class="ui-block-b"><input name="messageReply" type="submit" value="Submit" data-theme="b" /></div>
			</div>
		</form>
	<?php } ?>
<?php endblock() ?>