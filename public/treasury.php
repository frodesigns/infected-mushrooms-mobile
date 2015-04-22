<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';

	$query = sprintf("SELECT itemid, itemname, imageurl, history FROM items WHERE status = 1 ORDER BY itemname ASC");
	$result = mysql_query($query);
	
	include 'base-public.php';
?>

<?php startblock('title') ?>
	Treasury
<?php endblock() ?>

<?php startblock('header') ?>
	Treasury
<?php endblock() ?>

<?php startblock('content') ?>
	<ul data-role="listview">
	<?php
		$letterdivider = "";
		while ($row = mysql_fetch_assoc($result)) {
			$itemid = (int)$row['itemid'];
			$itemname = $row['itemname'];
			$imageurl = $row['imageurl'];
			$history = $row['history'];
			
			$letter = ucfirst($itemname[0]);
				
			if (!ctype_alnum($letter)) {
				$letter = "~";
			} else if (is_numeric($letter)) {
				$letter = "#";
			}
			
			if (!$history) {
				$history = "No history for this item.";
			}
			
			if ($letterdivider != $letter) {
				echo "<li data-role=\"list-divider\">$letter</li>";
				$letterdivider = $letter;
			}
			
			echo "<li class='treasurycontent'><img class='item-icon' src='http://www.frodesigns.com/im/$imageurl' title='$itemname' /> <h3>$itemname</h3><p><strong>History:</strong> $history</p></li>";
		}
	?>
	</ul>
<?php endblock() ?>