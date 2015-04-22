<?php
	include 'dbc.php';

	$query = sprintf("SELECT threadid FROM threads");
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	while ($row = mysql_fetch_assoc($result)) {
		$threadid = (int)$row['threadid'];
		
		$query2 = sprintf("SELECT id FROM users");
		$result2 = mysql_query($query2);
		$num_rows2 = mysql_num_rows($result2);
		
		while ($row2 = mysql_fetch_assoc($result2)) {
			$userid = (int)$row2['id'];
			
			$query3 = sprintf("SELECT * FROM readthreads WHERE id = $userid AND threadid = $threadid");
			$result3 = mysql_query($query3);
			$num_rows3 = mysql_num_rows($result3);
			
			if ($num_rows3 == 0) {
				$sql = "INSERT INTO readthreads (id, threadid, isread) VALUES ($userid, $threadid, 0)";
				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
			}			
		}
	}
	
	echo "Rows updated!";
?>