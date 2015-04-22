<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	page_protect();
	include $_SERVER['DOCUMENT_ROOT'] . '/currentuser.php';
	
	$pollid = $_GET['pollid'];

	if ($pollid) {
	
		$query = sprintf("SELECT * FROM polls WHERE pollid = $pollid");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows > 0) {
			
			$query2 = sprintf("SELECT * FROM votes WHERE pollid = $pollid AND id = $currentuserid");
			$result2 = mysql_query($query2);
			$num_rows2 = mysql_num_rows($result2);
			
			if ($num_rows2 == 0) {
			
				$sql = "INSERT INTO votes (id, pollid, vote) VALUES ($currentuserid, $pollid, 0)";

				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
			
			} else {
			
				$sql = "UPDATE votes SET vote = 0 WHERE pollid = $pollid AND id = $currentuserid";

				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
				
			}
			
		}
		
	}
?>