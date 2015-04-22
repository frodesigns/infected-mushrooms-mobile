<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Sent Messages on Mobile");
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

	$query2 = sprintf("SELECT messages.*, users.full_name AS 'receiver' 
		FROM messages 
		INNER JOIN users ON users.id = messages.toid
		WHERE fromid = $currentuserid 
		ORDER BY timestamp DESC
		LIMIT $start, $count");
	$result2 = mysql_query($query2);
	
	$query = sprintf("SELECT messageid
		FROM messages
		WHERE toid = $currentuserid");
	$result = mysql_query($query);
	$totalmessages = mysql_num_rows($result);
	
	$start = $start + $count;

	include 'base.php';
?>

<?php startblock('title') ?>
	Sent Messages
<?php endblock() ?>

<?php startblock('header') ?>
	Sent Messages
<?php endblock() ?>

<?php startblock('content') ?>
	<div class="ui-grid-a">
		<div class="ui-block-a"><a href="/messages.php" data-role="button" data-icon="arrow-l" data-iconpos="left" data-theme="a">Inbox</a></div>
		<div class="ui-block-b"><a href="/messagecreate.php" data-role="button" data-icon="plus" data-theme="b" >New Private Message</a></div>
	</div>
	<br />
	<ul id="messages-sent" data-role="listview"data-theme="a">
	<?php
		while ($row2 = mysql_fetch_assoc($result2)) {
			$messageid = $row2['messageid'];
			$replytomessageid = $row2['replytomessageid'];
			$receiver = $row2['receiver'];
			$title = $row2['title'];
			$isread = (int)$row2['isread'];
			if ($isread == 0) {
				$datatheme = "a";
			} else {
				$datatheme = "c";
			}
			$date = date("F j, Y \a\\t g:ia", strtotime($row2['timestamp']));
			
			echo "<li data-theme='$datatheme'><a href='/message.php?messageid=$messageid'><h3>$title</h3><p>Sent to <strong>$receiver</strong> on <em>$date</em></p></a></li>";
		}
	?>
	</ul>
	<br />
	<input type="hidden" id="messages-sent-start" value="<?php echo $start; ?>" />
	<input type="hidden" id="messages-sent-count" value="<?php echo $count; ?>" />
	<input type="hidden" id="messages-sent-total" value="<?php echo $totalmessages; ?>" />
	<button id="load-messages-sent" data-href="/ajax/loadmessages-sent.php" data-icon="refresh" data-theme="a" data-mini="true">Load More</button>
<?php endblock() ?>