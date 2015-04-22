<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Item Tracker on Mobile");
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
	
	$query = sprintf("SELECT itemid, itemname, missing, imageurl FROM items WHERE status = 1 ORDER BY itemname ASC");
	$result = mysql_query($query);
	
	include 'base.php';
?>

<?php startblock('title') ?>
	Item Tracker
<?php endblock() ?>

<?php startblock('header') ?>
	Item Tracker
<?php endblock() ?>

<?php startblock('content') ?>
	<ul data-role="listview">
	<?php
		$letterdivider = "";
		while ($row = mysql_fetch_assoc($result)) {
			$itemid = (int)$row['itemid'];
			$itemname = $row['itemname'];
			$imageurl = $row['imageurl'];
			$missing = $row['missing'];
			
			$query2 = sprintf("SELECT users.full_name, itemuser.timestamp, itemuser.reporterid, itemuser.status FROM users, itemuser, items WHERE items.itemid = $itemid AND items.itemid = itemuser.itemid AND users.id = itemuser.id ORDER BY itemuser.timestamp DESC LIMIT 1");
			$result2 = mysql_query($query2);
			
			$letter = ucfirst($itemname[0]);
				
			if (!ctype_alnum($letter)) {
				$letter = "~";
			} else if (is_numeric($letter)) {
				$letter = "#";
			}
			
			if ($missing == 1) {
				$datatheme = "a";
				$ismissing = "<span class='textred'> - Missing!</span>";
			} else {
				$datatheme = "c";
				$ismissing = "";
			}
			
			if ($letterdivider != $letter) {
				echo "<li data-role=\"list-divider\">$letter</li>";
				$letterdivider = $letter;
			}
			
			echo "<li data-theme='$datatheme' class='trackercontent'><a href='/item.php?itemid=$itemid'><img class='item-icon' src='http://www.frodesigns.com/im/$imageurl' title='$itemname' /> <h3>$itemname$ismissing</h3><p>";
			
			while ($row2 = mysql_fetch_assoc($result2)) {
				$date = date("F j, Y \a\\t g:ia", strtotime($row2['timestamp']));
				
				$reporterid = $row2['reporterid'];
			
				$query4 = sprintf("SELECT full_name FROM users WHERE id = $reporterid");
				$result4 = mysql_query($query4);
			
				while ($row4 = mysql_fetch_assoc($result4)) {
					$reportername = $row4['full_name'];
				}
				if ($row2['status'] == 0)
				{
					echo "<strong>" . $row2['full_name'] . "</strong> had this on " . $date . "<br /><small>Reported by <strong>$reportername</strong></small><br />";	
				} else {
					echo "<strong>" . $row2['full_name'] . "</strong> logged out with this item.<br /><small>$date</small><br />";
				}
			}
			
			echo "</p></a></li>";
		}
	?>
	</ul>
<?php endblock() ?>