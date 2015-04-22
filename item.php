<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';

	$itemname = "Item Not Found";
	$itemid = $_GET['itemid'];
	$success = $_GET['success'];
	
	if ($itemid) {
		$query = sprintf("SELECT itemname, history, missing, imageurl, reporterid FROM items WHERE itemid = $itemid");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0) {
			$itemid = null;
		} else {		
			while ($row = mysql_fetch_assoc($result)) {
				$itemname = $row['itemname'];
				$imageurl = $row['imageurl'];
				$history = $row['history'];
				$missing = $row['missing'];
				$reporterid = $row['reporterid'];
			}
			
			//activity update
			$activity = mysql_real_escape_string("Viewing <a href='http://immobile.frodesigns.com/item.php?itemid=$itemid'>$itemname</a> on Mobile");
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
			
			if (!$history) {
				$history = "No history for this item.";
			}
			
			$query2 = sprintf("SELECT users.full_name, itemuser.timestamp, itemuser.reporterid, itemuser.status FROM users, itemuser, items WHERE items.itemid = $itemid AND items.itemid = itemuser.itemid AND users.id = itemuser.id ORDER BY itemuser.timestamp DESC LIMIT 3");
			$result2 = mysql_query($query2);
			
			$query3 = sprintf("SELECT full_name, id FROM users WHERE (approved = 1 AND banned = 0 AND canvote = 1) OR id = 56 OR id = 326 ORDER BY full_name ASC");
			$result3 = mysql_query($query3);
			
			if ($_POST['updateSubmit']=='Update') {				
				$err = array();
				
				if ($_POST['item']) {	
					$id = $_POST['item'];				
					
					$query30 = sprintf("SELECT id FROM itemuser WHERE itemid = $itemid ORDER BY timestamp DESC LIMIT 1");
					$result30 = mysql_query($query30);
					while ($row30 = mysql_fetch_assoc($result30)) {
						$lastuserid = $row30['id'];
					}
					
					if ($id != $lastuserid) {
						$sql = "INSERT INTO itemuser (id, itemid, reporterid, status) VALUES ($id, $itemid, $currentuserid, 0)";

						if (!mysql_query($sql,$link))
						{
							die('Error: ' . mysql_error());
						}
						
						header("Location: /item.php?itemid=$itemid&success=true");
						
					} else {
						$err[] = "You can't give an item to the same person twice! Silly.";
					}		
				} else {
					$err[] = "Please select a recipient!";
				}
			}
			
			if ($_POST['loggedSubmit']=='Logging Out') {
				$err = array();
				
				$query40 = sprintf("SELECT id, status FROM itemuser WHERE itemid = $itemid ORDER BY timestamp DESC LIMIT 1");
				$result40 = mysql_query($query40);
				while ($row40 = mysql_fetch_assoc($result40)) {
					$lastloggedid = $row40['id'];
					$status = $row40['status'];
				}
				
				if ($currentuserid == $lastloggedid && $status == 1) {
					$err[] = "You can't log out with the same item twice! Silly.";
				} else {
					$sql = "INSERT INTO itemuser (id, itemid, reporterid, status) VALUES ($currentuserid, $itemid, $currentuserid, 1)";

					if (!mysql_query($sql,$link))
					{
						die('Error: ' . mysql_error());
					}
					
					header("Location: /item.php?itemid=$itemid&success=true");
				}
			}
			
			if ($_POST['flagSubmit']=='Report It') {
				$sql = "UPDATE items SET missing = 1, reporterid = $currentuserid WHERE itemid = $itemid";

				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
				
				header("Location: /item.php?itemid=$itemid&success=true");
			}
			
			if ($_POST['foundSubmit']=='Found It') {
				$sql = "UPDATE items SET missing = 0 WHERE itemid = $itemid";

				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}
				
				header("Location: /item.php?itemid=$itemid&success=true");
			}
		
		}
	}
	
	include 'base.php';
?>

<?php startblock('title') ?>
	<?php echo $itemname; ?> - Item Tracker
<?php endblock() ?>

<?php startblock('header') ?>
	<?php echo $itemname; ?> - Item Tracker
<?php endblock() ?>

<?php startblock('content') ?>
	<?php if (!$itemid) { ?>
		<h2><?php echo $itemname; ?></h2>
		<p>
			<a data-inline="true" data-rel="back" data-mini="true" data-theme="a" data-role="button" href="/tracker.php">Go Back</a>
		</p>
	<?php } else { ?>
		<h2><img class='item-page-icon' src='http://www.frodesigns.com/im/<?php echo $imageurl; ?>' title='<?php echo $itemname; ?>' /><?php echo $itemname; ?></h2>
		<?php
			/******************** ERROR MESSAGES*************************************************
			This code is to show error messages 
			**************************************************************************/
			if(!empty($err))  {
				echo "<p class=\"errors\">";
				foreach ($err as $e) {
					echo "$e <br>";
				}
				echo "</p>";	
			}
			/******************************* END ********************************/
			if ($success == "true") {
				echo "<p class=\"success\">Successfully Updated!</p>";
			}
		?>	
		<form action="/item.php?itemid=<?php echo $itemid; ?>" method="post">
			<p class="inline-select">
				Who currently has this item? 
				<select name='item' class='passedto' data-inline='true' data-mini='true'>
					<option value=''>Select a Name</option>
					<?php
						while ($row3 = mysql_fetch_assoc($result3)) {
							$id = $row3['id'];
							$value = $id;				
							echo "<option value='$value'>", $row3['full_name'] ,"</option>";		
						}
					?>
				</select>
				<input type='submit' name='updateSubmit' value='Update' data-inline='true' data-mini='true' data-theme="b" />
			</p>
			<p>
				Are you logging out with this item? <input type='submit' name='loggedSubmit' value='Logging Out' data-inline='true' data-mini='true' data-theme="b" />
			</p>
			<p>
			<?php
				if ($missing == 1) {
					$reporterid2 = $reporterid;
				
					$query5 = sprintf("SELECT full_name FROM users WHERE id = $reporterid2");
					$result5 = mysql_query($query5);
				
					while ($row5 = mysql_fetch_assoc($result5)) {
						$reportername2 = $row5['full_name'];
					}
					
					echo "<span class='found'>Flagged as missing by <strong>$reportername2</strong>!</span> <input type='submit' name='foundSubmit' value='Found It' data-inline='true' data-mini='true' data-theme='b' />";
				} else {
					echo "<span class='flag'>Is this item missing?</span> <input type='submit' name='flagSubmit' value='Report It' data-inline='true' data-mini='true' data-theme='b' />";
				}
			?>
			</p>
		</form>
		
		<hr />
		<p>
			<strong>History:</strong> <?php echo $history; ?>
		</p>
		<hr />
		<p class='itemhistory'>
			<?php
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
			?>
		</p>
		<hr />
		<p>
			<a data-inline="true" data-rel="back" data-mini="true" data-theme="a" data-role="button" href="/tracker.php">Go Back</a>
		</p>
	<?php } ?>

<?php endblock() ?>