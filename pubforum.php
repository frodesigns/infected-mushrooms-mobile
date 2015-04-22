<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Public Forum on Mobile");
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
	
	$start = 0;
	$count = 30;
	
	$query = sprintf("SELECT a.threadid, title, sticky, timestamp, authorid, updatedbyid, b.full_name as 'author', c.full_name as 'updatedby', isread, postcount, (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp ASC LIMIT 1) AS 'guestauthor', (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp DESC LIMIT 1) AS 'lastguestauthor'
		FROM threads a 
		LEFT JOIN users b on a.authorid = b.id
		LEFT JOIN users c on a.updatedbyid = c.id
		INNER JOIN readthreads d ON d.id = $currentuserid AND d.threadid = a.threadid
		WHERE private = 0
		ORDER BY sticky DESC, timestamp DESC
		LIMIT $start, $count");
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	$query1 = sprintf("SELECT a.threadid
		FROM threads a
		WHERE private = 0");
	$result1 = mysql_query($query1);
	$totalthreads = mysql_num_rows($result1);
	
	$start = $start + $count;
	
	include 'base.php';
?>

<?php startblock('title') ?>
	Public Forum
<?php endblock() ?>

<?php startblock('header') ?>
	Public Forum
<?php endblock() ?>

<?php startblock('content') ?>
	<div class="ui-grid-a">
		<div class="ui-block-a"><a href="/forum.php" data-role="button" data-icon="arrow-l" data-iconpos="left" data-theme="a">Private Forum</a></div>
		<div class="ui-block-b"><a href="/newthread.php" data-role="button" data-icon="plus" data-theme="b">New Thread</a></div>
	</div>
	<br />
	<ul id="pub-threads" data-role="listview">
	<?php
		$stickydivider = 1;
		while ($row = mysql_fetch_assoc($result)) {
			$threadid = (int)$row['threadid'];
			$title = $row['title'];
			$lastupdated = date("F j, Y \a\\t g:ia", strtotime($row['timestamp']));
			$authorid = $row['authorid'];
			$authorname = $row['author'];
			$lastauthorid = $row['updatedbyid'];
			$lastauthorname = $row['updatedby'];
			$created = $row['created'];
			$read = $row['isread'];
			$sticky = (int)$row['sticky'];
			$replies = $row['postcount'];
			$lastguestauthor = $row['lastguestauthor'];
			$guestauthor = $row['guestauthor'];
			
			if ($read == 1) {
				$datatheme = "c";
			} else {
				$datatheme = "a";
			}
			
			if ($stickydivider == $sticky) {
				if ($stickydivider == 1) {
					echo "<li data-role=\"list-divider\">Sticky Threads</li>";
					$stickydivider = 0;
				} else {
					echo "<li data-role=\"list-divider\">Regular Threads</li>";
					$stickydivider = 1;
				}
			}
			
			if ($guestauthor != "") {
				$authorname = $guestauthor;
			}
			
			if ($lastguestauthor != "") {
				$lastauthorname = $lastguestauthor;
			}
			
			if ($sticky == 1) {
				echo "<li data-theme='$datatheme'><a href='/thread.php?threadid=$threadid'>
				<h3><em>Sticky:</em> $title</h3> 
				<p>
					<strong>Posted by:</strong> $authorname<br />
					<strong>Updated on:</strong> <em>$lastupdated</em> by <em>$lastauthorname</em>
				</p>
				<span class='ui-li-count'>$replies replies</span>
				</a></li>";
			} else {
				echo "<li data-theme='$datatheme'><a href='/thread.php?threadid=$threadid'>
				<h3>$title</h3> 
				<p>
					<strong>Posted by:</strong> $authorname<br />
					<strong>Updated on:</strong> <em>$lastupdated</em> by <em>$lastauthorname</em>
				</p>
				<span class='ui-li-count'>$replies replies</span>
				</a></li>";
			}

		}
	?>
	</ul>
	<br />
	<input type="hidden" id="pub-start" value="<?php echo $start; ?>" />
	<input type="hidden" id="pub-count" value="<?php echo $count; ?>" />
	<input type="hidden" id="pub-total" value="<?php echo $totalthreads; ?>" />
	<button id="load-pub-threads" data-href="/ajax/loadpubthreads.php" data-icon="refresh" data-theme="a" data-mini="true">Load More</button>
<?php endblock() ?>