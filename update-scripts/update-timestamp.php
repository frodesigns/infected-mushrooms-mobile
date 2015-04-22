<?php
	include 'dbc.php';

	$query = sprintf("SELECT a.threadid, timestamp, (SELECT timestamp FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp DESC LIMIT 1) AS 'updated' FROM threads a WHERE private = 0");
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	while ($row = mysql_fetch_assoc($result)) {
		$threadid = (int)$row['threadid'];
		$updated = $row['updated'];
		
		mysql_select_db($db, $link);
		
		$sql = "UPDATE threads SET timestamp = '$updated' WHERE threadid = $threadid";
		
		if (!mysql_query($sql,$link))
		{
			die('Error: ' . mysql_error());
		}
	}
	
	echo "Rows updated!";
?>