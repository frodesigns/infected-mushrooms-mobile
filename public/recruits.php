<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';

	$query = sprintf("SELECT a.pollid, title, description, a.timestamp, c.full_name AS 'createdby', (SELECT COUNT(*) FROM votes b WHERE a.pollid = b.pollid AND vote = 0) AS 'no', (SELECT COUNT(*) FROM votes b WHERE a.pollid = b.pollid AND vote = 1) AS 'yes' 
		FROM polls a 
		INNER JOIN users c ON c.id = a.createdbyid
		WHERE isrecruit = 1 
		ORDER BY title ASC");
	$result = mysql_query($query);
	$num_recruits = mysql_num_rows($result);
	
	$query3 = sprintf("SELECT COUNT(*) FROM users WHERE canvote = 1 AND banned = 0 AND approved = 1");
	$result3 = mysql_query($query3);
	while ($row3 = mysql_fetch_assoc($result3)) {
		$votetotal = (int)$row3['COUNT(*)'];
	}
	
	include 'base-public.php';
?>

<?php startblock('title') ?>
	Recruits
<?php endblock() ?>

<?php startblock('header') ?>
	Recruits
<?php endblock() ?>

<?php startblock('content') ?>		
	<a href="/public/newthread-public.php?type=app" data-role="button" data-icon="plus" data-theme="e">Apply for Membership</a>
	<ul data-role="listview" data-inset="true" data-theme="a">
		<?php 
			if ($num_recruits > 0) { 
				while ($row = mysql_fetch_assoc($result)) {
					$pollid = $row['pollid'];
					$title = $row['title'];
					$description = $row['description'];
					$yes = (int)$row['yes'];
					$no = (int)$row['no'];			
					$timestamp = date("F j, Y \a\\t g:ia", strtotime($row['timestamp']));
					$createdby = $row['createdby'];
					
					$yespercent = round(($yes / $votetotal) * 100);
					$nopercent = round(($no / $votetotal) * 100);
					
					echo "<li id='$pollid' class='pollcontent'><h3>$title</h3><p>";
					echo "Added on $timestamp by $createdby<br /><br />$description<hr />"; 
					
					echo "<div data-role='fieldcontain' class='yes'>";
					echo "<label for='yes-$pollid'>Yes %:</label>";
					echo "<input disabled type='range' name='slider-yes-$pollid' id='yes-$pollid' value='$yespercent' min='0' max='100' data-highlight='true' />";
					echo "</div>";
					
					echo "<div data-role='fieldcontain' class='no'>";
					echo "<label for='no-$pollid'>No %:</label>";
					echo "<input disabled type='range' name='slider-no-$pollid' id='no-$pollid' value='$nopercent' min='0' max='100' data-highlight='true' /></p>";
					echo "</div></li>";
				}
			} else {
				echo "<li data-theme='c'>There are no new recruit polls at this time.</li>";
			}
		?>	
	</ul>
<?php endblock() ?>