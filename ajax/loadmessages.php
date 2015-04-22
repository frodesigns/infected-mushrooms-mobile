<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	page_protect();
	include $_SERVER['DOCUMENT_ROOT'] . '/currentuser.php';
	
	$start = $_GET['start'];
	$count = $_GET['count'];
	
	if (!$start || !(int)($start)) {
		$start = 0;
	}
	
	if (!$count || !(int)($count)) {
		$count = 30;
	}
	
	$query = sprintf("SELECT messages.*, users.full_name AS 'sender' 
		FROM messages 
		INNER JOIN users ON users.id = messages.fromid
		WHERE toid = $currentuserid 
		ORDER BY timestamp DESC
		LIMIT $start, $count");
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	while ($row = mysql_fetch_assoc($result)) {
		$messageid = $row['messageid'];
		$replytomessageid = $row['replytomessageid'];
		$sender = $row['sender'];
		$title = $row['title'];
		$isread = (int)$row['isread'];
		if ($isread == 0) {
			$datatheme = "a";
		} else {
			$datatheme = "c";
		}
		$date = date("F j, Y \a\\t g:ia", strtotime($row['timestamp']));
		
		echo "<li data-theme='$datatheme'><a href='/message.php?messageid=$messageid'><h3>$title</h3><p>Sent by <strong>$sender</strong> on <em>$date</em></p></a></li>";
	}
?>