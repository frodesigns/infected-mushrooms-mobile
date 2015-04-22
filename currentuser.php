<?php
	if (isset($_SESSION['user_id'])) {
		$currentuserid = $_SESSION['user_id'];
		
		$query20 = sprintf("SELECT full_name FROM users WHERE id = $currentuserid");
		$result20 = mysql_query($query20);
		
		while ($row20 = mysql_fetch_assoc($result20)) {
			$currentusername = $row20['full_name'];
		}
	}
?>