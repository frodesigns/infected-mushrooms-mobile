<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';

	$start = 0;
	$count = 30;
	
	$query = sprintf("SELECT a.threadid, title, sticky, timestamp, authorid, updatedbyid, b.full_name as 'author', c.full_name as 'updatedby', postcount, (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp ASC LIMIT 1) AS 'guestauthor', (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp DESC LIMIT 1) AS 'lastguestauthor'
		FROM threads a 
		LEFT JOIN users b on a.authorid = b.id
		LEFT JOIN users c on a.updatedbyid = c.id
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
	
	include 'base-public.php';
?>

<?php startblock('title') ?>
	Public Forum
<?php endblock() ?>

<?php startblock('header') ?>
	Public Forum
<?php endblock() ?>

<?php startblock('content') ?>
	<div class="ui-grid-a">
		<div class="ui-block-a"><a href="/public/newthread-public.php" data-role="button" data-icon="plus" data-theme="b">New Thread</a></div>
		<div class="ui-block-b"><a href="/public/newthread-public.php?type=app" data-role="button" data-icon="plus" data-theme="e">Apply for Membership</a></div>
	</div>
	<br />
	<ul id="pub-public-threads" data-role="listview">
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
			$sticky = (int)$row['sticky'];
			$replies = $row['postcount'];
			$lastguestauthor = $row['lastguestauthor'];
			$guestauthor = $row['guestauthor'];
			
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
				echo "<li><a href='/public/thread-public.php?threadid=$threadid'>
				<h3><em>Sticky:</em> $title</h3> 
				<p>
					<strong>Posted by:</strong> $authorname<br />
					<strong>Updated on:</strong> <em>$lastupdated</em> by <em>$lastauthorname</em>
				</p>
				<span class='ui-li-count'>$replies replies</span>
				</a></li>";
			} else {
				echo "<li><a href='/public/thread-public.php?threadid=$threadid'>
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
	<input type="hidden" id="pub-public-start" value="<?php echo $start; ?>" />
	<input type="hidden" id="pub-public-count" value="<?php echo $count; ?>" />
	<input type="hidden" id="pub-public-total" value="<?php echo $totalthreads; ?>" />
	<button id="load-pub-threads-public" data-href="/ajax/loadpubthreads-public.php" data-icon="refresh" data-theme="a" data-mini="true">Load More</button>
<?php endblock() ?>