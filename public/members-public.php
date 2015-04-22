<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	
	$query = sprintf("SELECT * FROM users WHERE approved = 1 AND banned = 0 AND id <> 58 ORDER BY full_name ASC");
	$result = mysql_query($query);
	
	include 'base-public.php';
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
				
				echo "<li>";
				echo "<span class='ui-li-aside'>" . $admin . "<br /><br />" . $active . "</span>";
				echo "<h3>" . $row['full_name'] . "</h3><p>" . $row['real_name'] . " - " . $row['country'] . "</p>";				
				echo "</li>";
			}
		?>
	</ul>
<?php endblock() ?>