<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/dbc.php';
	
	$threadtitle = "Thread Not Found";
	$threadid = $_GET['threadid'];
	
	if ($threadid) {
		$query = sprintf("SELECT title, sticky FROM threads WHERE threadid = $threadid AND private = 0");
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0) {
			$threadid = null;
		} else {
		
			while ($row = mysql_fetch_assoc($result)) {
				$threadtitle = $row['title'];
				$sticky = $row['sticky'];
			}			
		}
	}
	
	include 'base-public.php';
?>

<?php startblock('title') ?>
	<?php echo $threadtitle; ?> - Public Forum
<?php endblock() ?>

<?php startblock('header') ?>
	<?php echo $threadtitle; ?> - Public Forum
<?php endblock() ?>

<?php startblock('content') ?>
	<?php if (!$threadid) { ?>
		<h2><?php echo $threadtitle; ?></h2>
	<?php } else { ?>
		<h2><?php echo $threadtitle; ?></h2>
		<div class="ui-grid-a">
			<div class="ui-block-a"><a href="/public/pubforum-public.php" data-role="button" data-rel="back" data-icon="arrow-l" data-theme="a">Go Back</a></div>
			<div class="ui-block-b"><a href="/public/threadreply-public.php?threadid=<?php echo $threadid; ?>" data-role="button" data-icon="plus" data-theme="b">Post Reply</a></div>
		</div>
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
	<?php } ?>
<?php endblock() ?>