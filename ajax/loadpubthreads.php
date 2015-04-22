<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	page_protect();
	include $_SERVER['DOCUMENT_ROOT'] . '/currentuser.php';
	
	$start = $_GET['start'];
	$count = $_GET['count'];
	
	if (!$start || !(int)($start)) {
		$start = 0;
	}
	
	if (!$count || !(int)($count)) {
		$count = 30;
	}
	
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