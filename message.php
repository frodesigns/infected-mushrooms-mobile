<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Messages on Mobile");
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
				$messageid = (int)$row['messageid'];
				$messagetitle = $row['title'];
				$content = str_replace("tiny_mce/plugins/emotions/img/", "http://www.frodesigns.com/im/tiny_mce/plugins/emotions/img/", $row['content']);
				$isread = $row['isread']; 
				$timestamp = date("F j, Y \a\\t g:ia", strtotime($row['timestamp']));
				$sender = $row['sender'];
				$receiver = $row['receiver'];
				$toid = $row['toid'];
				$fromid = $row['fromid'];
			}
			
			if ($toid != $currentuserid && $fromid != $currentuserid) {
				$messageid = null;
				$messagetitle = "Message Not Found";
			}
			
			if ($toid == $currentuserid) {
				$sql = "UPDATE messages SET isread = 1 WHERE messageid = $messageid";
				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
			}
		}
		
	}
	
	include 'base.php';
?>

<?php startblock('title') ?>
	<?php echo $messagetitle; ?> - Messages
<?php endblock() ?>

<?php startblock('header') ?>
	<?php echo $messagetitle; ?> - Messages
<?php endblock() ?>

<?php startblock('content') ?>
	<?php if (!$messageid) { ?>
		<h2><?php echo $messagetitle; ?></h2>
		<p>
			<a data-inline="true" data-rel="back" data-mini="true" data-theme="a" data-role="button" href="/messages.php">Go Back</a>
		</p>
	<?php } else { ?>
		<h2><?php echo $messagetitle; ?></h2>
		<?php if ($toid == $currentuserid) { ?>
			<a href="/messagereply.php?messageid=<?php echo $messageid; ?>" data-role="button" data-icon="plus" data-theme="b" >Reply</a>
		<?php } ?>
		<p>
			<?php if ($toid == $currentuserid) { ?>
				Sent by <strong><?php echo $sender; ?></strong> on <em><?php echo $timestamp; ?></em>
			<?php } else { ?>
				Sent to <strong><?php echo $receiver; ?></strong> on <em><?php echo $timestamp; ?></em>
			<?php } ?>
		</p>
		<hr />
		<p class="messagecontent">
			<?php echo $content; ?>
		</p>
		<hr />
		<p>
			<a data-inline="true" data-rel="back" data-mini="true" data-theme="a" data-role="button" href="/messages.php">Go Back</a>
		</p>
	<?php } ?>
<?php endblock() ?>