<?php
	include 'dbc.php';

	$query = sprintf("SELECT a.threadid, (SELECT COUNT(*) FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0) AS 'postcount' FROM threads a");
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	while ($row = mysql_fetch_assoc($result)) {
		$threadid = (int)$row['threadid'];
		$postcount = $row['postcount'];
		$postcount = $postcount - 1;
		
		mysql_select_db($db, $link);
		
		$sql = "UPDATE threads SET postcount = $postcount WHERE threadid = $threadid";
		
		if (!mysql_query($sql,$link))
		{
			die('Error: ' . mysql_error());
		}
	}
	
	echo "Rows updated!";
?>