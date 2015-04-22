<?php
	include 'dbc.php';

	$query = sprintf("SELECT a.threadid, (SELECT authorid FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp ASC LIMIT 1) AS 'authorid', (SELECT authorid FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp DESC LIMIT 1) AS 'lastauthorid', (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp ASC LIMIT 1) AS 'guestauthor', (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp DESC LIMIT 1) AS 'lastguestauthor' FROM threads a WHERE private = 0");
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	while ($row = mysql_fetch_assoc($result)) {
		$threadid = (int)$row['threadid'];
		$authorid = (int)$row['authorid'];
		$lastauthorid = (int)$row['lastauthorid'];
		$guestauthor = $row['guestauthor'];
		$lastguestauthor = $row['lastguestauthor'];
		
		if ($guestauthor != "") {
			$authorid = "NULL";
		}
		
		if ($lastguestauthor != "") {
			$lastauthorid = "NULL";
		}
		
		mysql_select_db($db, $link);
		
		$sql = "UPDATE threads SET authorid = $authorid, updatedbyid = $lastauthorid WHERE threadid = $threadid";
		
		if (!mysql_query($sql,$link))
		{
			die('Error: ' . mysql_error());
		}
	}
	
	echo "Rows updated!";
?>