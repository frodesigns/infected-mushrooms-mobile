<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	$limit = 30;
	
	//activity update
	$activity = "Viewing Dashboard on Mobile";
	$sql = "UPDATE users SET lastactivity = '$activity', lastactivitytime = CURRENT_TIMESTAMP WHERE id = $currentuserid";
	if (!mysql_query($sql,$link))
	{
		die('Error: ' . mysql_error());
	}
	
	//unread priv threads
	$query1 = sprintf("SELECT threads.title, threads.threadid, threads.timestamp, users.full_name AS 'updatedby' 
		FROM threads 
		INNER JOIN users ON users.id = threads.updatedbyid
		INNER JOIN readthreads ON readthreads.id = $currentuserid AND readthreads.threadid = threads.threadid 
		WHERE threads.private = 1 AND readthreads.isread != 1
		ORDER BY threads.timestamp DESC
		LIMIT $limit");
	$result1 = mysql_query($query1);
	$privthreads = mysql_num_rows($result1);
	
	if ($privthreads > $limit) {
		$privthreads = $limit . "+";
	}

	//unread pub threads
	$query2 = sprintf("SELECT a.threadid, title, timestamp, c.full_name as 'updatedby', (SELECT guestname FROM posts b WHERE b.threadid = a.threadid AND b.spam = 0 ORDER BY timestamp DESC LIMIT 1) AS 'lastguestauthor'
		FROM threads a 
		LEFT JOIN users b on a.authorid = b.id
		LEFT JOIN users c on a.updatedbyid = c.id
		INNER JOIN readthreads d ON d.id = $currentuserid AND d.threadid = a.threadid
		WHERE private = 0  AND d.isread != 1
		ORDER BY timestamp DESC
		LIMIT $limit");
	$result2 = mysql_query($query2);
	$pubthreads = mysql_num_rows($result2);
	
	if ($pubthreads > $limit) {
		$pubthreads = $limit . "+";
	}
	
	//new polls
	$query3 = sprintf("SELECT title, pollid, isrecruit, timestamp, users.full_name AS 'createdby'
		FROM polls 
		INNER JOIN users ON users.id = polls.createdbyid
		WHERE not exists (SELECT * FROM votes WHERE polls.pollid = votes.pollid AND id = $currentuserid) 
		ORDER BY title ASC");
	$result3 = mysql_query($query3);
	$polls = mysql_num_rows($result3);	
	
	//unread messages
	$query4 = sprintf("SELECT title, messageid, timestamp, users.full_name AS 'sender'
		FROM messages 
		INNER JOIN users ON messages.fromid = users.id
		WHERE toid = $currentuserid AND isread = 0
		LIMIT $limit");
	$result4 = mysql_query($query4);
	$unreadmessages = mysql_num_rows($result4);
	
	if ($unreadmessages > $limit) {
		$unreadmessages = $limit . "+";
	}
	
	$query5 = sprintf("SELECT itemid, itemname, users.full_name as 'reporter' 
		FROM items 
		INNER JOIN users ON items.reporterid = users.id
		WHERE missing = 1 
		ORDER BY itemname DESC");
	$result5 = mysql_query($query5);
	$missingitems = mysql_num_rows($result5);
	
	//who's online
	$time = time();
	$previous = "300";
	$timeout = $time-$previous;
	$onlinequery = "SELECT * FROM online WHERE id=$currentuserid";
	$verify = mysql_query($onlinequery);
	$row_verify = mysql_fetch_assoc($verify);
	if (!isset($row_verify['id'])) {
		$addonlinequery = "INSERT INTO online (id, timeout) VALUES ($currentuserid, \"$time\")";
		$insert = mysql_query($addonlinequery);
	} else {
		$updateonlinequery = "UPDATE online SET timeout = \"$time\" WHERE id = $currentuserid";
		$update = mysql_query($updateonlinequery);
	}
	$whosonlinequery = "SELECT online.*, users.full_name AS 'username', users.lastactivity, users.lastactivitytime
		FROM online 
		INNER JOIN users ON users.id = online.id
		WHERE timeout > \"$timeout\" 
		ORDER BY username ASC";
	$online = mysql_query($whosonlinequery);
	$row_online = mysql_fetch_assoc($online);
	$num_online = mysql_num_rows($online);
	$onlinelist;	
	if (isset($row_online['username'])) {
		do {			
			$lastactivity = str_replace("/im/", "/", $row_online['lastactivity']);
			$onlinelist .= "<li>" . $row_online['username'] . "<br /><small>" . $lastactivity . " on " . date("F j, Y \a\\t g:ia", strtotime($row_online['lastactivitytime'])) . "</small></li>";
		} while($row_online = mysql_fetch_assoc($online)); 
	} else { 
		$onlinelist = "<li>There are no members online.</li>";
	} 
	
	include 'base.php';
?>

<?php startblock('title') ?>
	Dashboard
<?php endblock() ?>

<?php startblock('header') ?>
	Dashboard
<?php endblock() ?>

<?php startblock('content') ?>
	<h2 class="mushroom"><?php echo $currentusername; ?>'s Dashboard</h2>
	
	<ul id="privateMessages" data-role="listview" data-inset="true" data-theme="a">
		<li data-role="list-divider">Private Messages - <?php echo $unreadmessages; ?></li>
	<?php
		if ($unreadmessages > 0) {		
			while ($row4 = mysql_fetch_assoc($result4)) {
				$messagetitle = $row4['title'];	
				$messageid = $row4['messageid'];				
				$lastupdated = date("F j, Y \a\\t g:ia", strtotime($row4['timestamp']));
				$sender = $row4['sender'];
				echo "<li><a href='/message.php?messageid=$messageid'>$messagetitle<br /><small>Sent by $sender on $lastupdated</small></a></li>";
			}
		} else {
			echo "<li data-theme='c'>No new private messages.</li>";
		}
	?>
	</ul>
	
	<?php if ($missingitems > 0) { ?>
		<ul data-role="listview" data-inset="true" data-theme="e">
			<li data-role="list-divider">Missing Items</li>
			<?php
				while ($row5 = mysql_fetch_assoc($result5)) {
					$itemid = $row5['itemid'];
					$itemname = $row5['itemname'];	
					$reporter = $row5['reporter'];				
					echo "<li><a href='/item.php?itemid=$itemid'>$itemname<br /><small>Reported by $reporter</small></a></li>";
				}
			?>
		</ul>
	<?php } ?>

	<div data-role="collapsible">
		<h3>Unread Private Forum Posts - <?php echo $privthreads; ?></h3>		
		<ul data-role="listview" data-inset="true" data-theme="a">
		<?php
			if ($privthreads > 0) {		
				while ($row1 = mysql_fetch_assoc($result1)) {
					$threadtitle = $row1['title'];
					$threadid = $row1['threadid'];			
					$lastupdated = date("F j, Y \a\\t g:ia", strtotime($row1['timestamp']));
					$updatedby = $row1['updatedby'];
					echo "<li><a href='/thread.php?threadid=$threadid'>$threadtitle<br /><small>Updated $lastupdated by $updatedby</small></a></li>";
				}
			} else {
				echo "<li>No unread private posts.</li>";
			}
		?>
		</ul>
	</div>
	<div data-role="collapsible">
		<h3>Unread Public Forum Posts - <?php echo $pubthreads; ?></h3>
		<ul data-role="listview" data-inset="true" data-theme="a">
		<?php
			if ($pubthreads > 0) {		
				while ($row2 = mysql_fetch_assoc($result2)) {
					$threadtitle = $row2['title'];
					$threadid = $row2['threadid'];		
					$lastupdated = date("F j, Y \a\\t g:ia", strtotime($row2['timestamp']));	
					$updatedby = $row2['updatedby'];
					$lastguestauthor = $row2['lastguestauthor'];
					
					if ($lastguestauthor != "") {
						$updatedby = $lastguestauthor;
					}
			
					echo "<li><a href='/thread.php?threadid=$threadid'>$threadtitle<br /><small>Updated $lastupdated by $updatedby</small></a></li>";
				}
			} else {
				echo "<li>No unread public posts.</li>";
			}
		?>
		</ul>
	</div>
	<div data-role="collapsible">
		<h3>New Polls - <?php echo $polls; ?></h3>
		<ul data-role="listview" data-inset="true" data-theme="a">
		<?php
			if ($polls > 0) {		
				while ($row3 = mysql_fetch_assoc($result3)) {
					$polltitle = $row3['title'];	
					$pollid = $row3['pollid'];		
					$lastupdated = date("F j, Y \a\\t g:ia", strtotime($row3['timestamp']));	
					$createdby = $row3['createdby'];
					$isrecruit = $row3['isrecruit'];
					
					if ($isrecruit == 0) {
						$polltype = "Private Poll";
					} else {
						$polltype = "New Recruit";
					}
					echo "<li><a href='/polls.php'>$polltitle - <em>$polltype</em><br /><small>Added $lastupdated by $createdby</small></a></li>";
				}
			} else {
				echo "<li>No new polls.</li>";
			}
		?>
		</ul>
	</div>
	<ul data-role="listview" data-inset="true" data-theme="a">
		<li data-role="list-divider">Who's Online - <?php echo $num_online; ?></li>
		<?php echo $onlinelist; ?>
	</ul>
<?php endblock() ?>