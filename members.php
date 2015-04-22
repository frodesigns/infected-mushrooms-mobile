<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Members on Mobile");
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
	
	$query = sprintf("SELECT * FROM users WHERE approved = 1 AND banned = 0 ORDER BY full_name ASC");
	$result = mysql_query($query);
	
	include 'base.php';
?>

<?php startblock('title') ?>
	Members
<?php endblock() ?>

<?php startblock('header') ?>
	Members
<?php endblock() ?>

<?php startblock('content') ?>
	<ul data-role="listview">
		<?php
			$letterdivider = "";
			while ($row = mysql_fetch_assoc($result)) {
				$active = "Inactive/Trial Member";
				$admin = "Regular Member";
				if ($row['canvote'] == 1) {
					$active = "<span class='textgreen'>Active/Full Member</span>";
				}
				if ($row['user_level'] == 5) {
					$admin = "<span class='textred'>Adminstrator</span>";
				}
				
				$letter = ucfirst($row['full_name'][0]);
				
				if (!ctype_alnum($letter)) {
					$letter = "~";
				} else if (is_numeric($letter)) {
					$letter = "#";
				}
				
				if ($letterdivider != $letter) {
					echo "<li data-role=\"list-divider\">$letter</li>";
					$letterdivider = $letter;
				}
				
				echo "<li class='membercontent'>";
				echo "<span class='ui-li-aside'>" . $admin . "<br /><br />" . $active . "</span>";
				echo "<h3>" . $row['full_name'] . "</h3><p>" . $row['real_name'] . " - " . $row['country'];
				$lastactivity = str_replace("/im/", "/", $row['lastactivity']);
				echo "<br /><small>Last seen " . $lastactivity . " on " . date("F j, Y \a\\t g:ia", strtotime($row['lastactivitytime'])) . "</small>";
				
				if ($row['char1name'] == "" && $row['char2name'] == "" && $row['char3name'] == "" && $row['char4name'] == "") {
				
				} else {
					echo "<br /><br /><b><u>Characters</u></b>";
					if ($row['char1name'] != "") {
						echo "<br />" . $row['char1name'] . " - " . $row['char1lvl'] . " " . $row['char1type'];
					}
					if ($row['char2name'] != "") {
						echo "<br />" . $row['char2name'] . " - " . $row['char2lvl'] . " " . $row['char2type'];
					}
					if ($row['char3name'] != "") {
						echo "<br />" . $row['char3name'] . " - " . $row['char3lvl'] . " " . $row['char3type'];
					}
					if ($row['char4name'] != "") {
						echo "<br />" . $row['char4name'] . " - " . $row['char4lvl'] . " " . $row['char4type'];
					}
				}
				
				echo "</p>";
				
				echo "</li>";
			}
		?>
	</ul>
<?php endblock() ?>