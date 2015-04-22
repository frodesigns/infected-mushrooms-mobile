<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	$threadtitle = "Thread Not Found";
	$threadid = $_GET['threadid'];
	
	if ($threadid) {
		$query = sprintf("SELECT title, private, sticky FROM threads WHERE threadid = $threadid");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0) {
			$threadid = null;
		} else {
		
			while ($row = mysql_fetch_assoc($result)) {
				$threadtitle = $row['title'];
				$private = $row['private'];
				$sticky = $row['sticky'];
				
				//activity update
				$activity = mysql_real_escape_string("Viewing <a href='/im/thread.php?threadid=$threadid'>$threadtitle</a> on Mobile");
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
				
				if ($private == 1 && !isset($_SESSION['user_id'])) {
					header("Location: forum.php");
				}
				if ($private == 1) {
					$forumtype = "Private";
				} else {
					$forumtype = "Public";
				}
			}
			
			$query4 = sprintf("SELECT isread FROM readthreads WHERE threadid = $threadid AND id = $currentuserid");
			$result4 = mysql_query($query4);
			$readrows = mysql_num_rows($result4);
			if ($readrows == 0) {
				$sql = "INSERT INTO readthreads (id, threadid, isread) VALUES ($currentuserid, $threadid, 1)";
				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}	
			} else {
				$sql = "UPDATE readthreads SET isread = 1 WHERE threadid = $threadid AND id = $currentuserid";
				if (!mysql_query($sql,$link))
				{
					die('Error: ' . mysql_error());
				}	
			}
			
		}
	}
	
	include 'base.php';
?>

<?php startblock('title') ?>
	<?php echo $threadtitle; ?> - <?php echo $forumtype; ?> Forum
<?php endblock() ?>

<?php startblock('header') ?>
	<?php echo $threadtitle; ?> - <?php echo $forumtype; ?> Forum
<?php endblock() ?>

<?php startblock('content') ?>
	<?php if (!$threadid) { ?>
		<h2><?php echo $threadtitle; ?></h2>
	<?php } else { ?>
		<h2><?php echo $threadtitle; ?></h2>		
		<a href="/forum.php" data-role="button" data-rel="back" data-icon="arrow-l" data-theme="a">Go Back</a>
		<br />
		<ul data-role="listview">
		<?php
			$query2 = sprintf("SELECT postid, content, authorid, guestname, guestemail, timestamp FROM posts WHERE threadid = $threadid AND spam = 0 ORDER BY timestamp ASC");
			$result2 = mysql_query($query2);
			$num_rows = mysql_num_rows($result2);
			$i = 1;
			while ($row2 = mysql_fetch_assoc($result2)) {
				$postid = (int)$row2['postid'];
				$content = str_replace("tiny_mce/plugins/emotions/img/", "http://www.frodesigns.com/im/tiny_mce/plugins/emotions/img/", $row2['content']);
				$authorid = $row2['authorid'];
				$guestname = $row2['guestname'];
				$guestemail = $row2['guestemail'];
				$timestamp = date("F j, Y \a\\t g:ia", strtotime($row2['timestamp']));
				
				if ($guestname != "") {
					$authorname = $guestname;
					$authorcolor = "italicauth";
					$emailhash = md5(strtolower(trim("$guestemail")));
					$authorid = "";
				} else {
					$authorid = (int)$authorid;
					$query19 = sprintf("SELECT full_name, user_email FROM users WHERE id = $authorid");
					$result19 = mysql_query($query19);
					while ($row19 = mysql_fetch_assoc($result19)) {
							$authorname = $row19['full_name'];
							$email = $row19['user_email'];
							$emailhash = md5(strtolower(trim("$email")));
							$defaultimage = urlencode('http://www.frodesigns.com/im/images/mushroom-small.png');
					}		
					$authorcolor = "boldauth";
				}
				
				echo "<li class='postcontent'>";
				// if ($guestname != "") {			
					// echo "<img src='http://www.gravatar.com/avatar/$emailhash?d=identicon' />";
				// } else {
					// echo "<img class='item-icon' src='http://www.gravatar.com/avatar/$emailhash?d=$defaultimage' />";
				// }
				echo "<h3>$authorname wrote on $timestamp:</h3> 
				<p>
					$content
				</p>
				</li>";
			}
		?>
		</ul>
		<br />
		<a href="/threadreply.php?threadid=<?php echo $threadid; ?>" data-role="button" data-icon="plus" data-theme="b">Post Reply</a>
	<?php } ?>
<?php endblock() ?>