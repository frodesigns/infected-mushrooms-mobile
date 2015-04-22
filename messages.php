<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Message Inbox on Mobile");
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
	
	$start = 0;
	$count = 30;

	$query1 = sprintf("SELECT messages.*, users.full_name AS 'sender' 
		FROM messages 
		INNER JOIN users ON users.id = messages.fromid
		WHERE toid = $currentuserid 
		ORDER BY timestamp DESC
		LIMIT $start, $count");
	$result1 = mysql_query($query1);
	
	$query = sprintf("SELECT messageid
		FROM messages
		WHERE toid = $currentuserid");
	$result = mysql_query($query);
	$totalmessages = mysql_num_rows($result);
	
	$start = $start + $count;
	
	include 'base.php';
?>

<?php startblock('title') ?>
	Message Inbox
<?php endblock() ?>

<?php startblock('header') ?>
	Message Inbox
<?php endblock() ?>

<?php startblock('content') ?>
	<div class="ui-grid-a">
		<div class="ui-block-a"><a href="/messagecreate.php" data-role="button" data-icon="plus" data-theme="b" >New Private Message</a></div>
		<div class="ui-block-b"><a href="/messages-sent.php" data-role="button" data-icon="arrow-r" data-iconpos="right" data-theme="a">Sent Box</a></div>
	</div>
	<br />
	<ul id="messages" data-role="listview" data-theme="a">
	<?php
		while ($row1 = mysql_fetch_assoc($result1)) {
			$messageid = $row1['messageid'];
			$replytomessageid = $row1['replytomessageid'];
			$sender = $row1['sender'];
			$title = $row1['title'];
			$isread = (int)$row1['isread'];
			if ($isread == 0) {
				$datatheme = "a";
			} else {
				$datatheme = "c";
			}
			$date = date("F j, Y \a\\t g:ia", strtotime($row1['timestamp']));
			
			echo "<li data-theme='$datatheme'><a href='/message.php?messageid=$messageid'><h3>$title</h3><p>Sent by <strong>$sender</strong> on <em>$date</em></p></a></li>";
		}
	?>
	</ul>
	<br />
	<input type="hidden" id="messages-start" value="<?php echo $start; ?>" />
	<input type="hidden" id="messages-count" value="<?php echo $count; ?>" />
	<input type="hidden" id="messages-total" value="<?php echo $totalmessages; ?>" />
	<button id="load-messages" data-href="/ajax/loadmessages.php" data-icon="refresh" data-theme="a" data-mini="true">Load More</button>
<?php endblock() ?>