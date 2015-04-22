<?php
	include 'dbc.php';
	page_protect();
	include 'currentuser.php';
	
	//activity update
	$activity = mysql_real_escape_string("Viewing Polls on Mobile");
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
	
	$query = sprintf("SELECT a.pollid, title, description, a.timestamp, c.full_name AS 'createdby', d.vote, (SELECT COUNT(*) FROM votes b WHERE a.pollid = b.pollid AND vote = 0) AS 'no', (SELECT COUNT(*) FROM votes b WHERE a.pollid = b.pollid AND vote = 1) AS 'yes' 
		FROM polls a 
		INNER JOIN users c ON c.id = a.createdbyid
		LEFT JOIN votes d ON d.pollid = a.pollid AND d.id = $currentuserid
		WHERE isrecruit = 1 
		ORDER BY title ASC");
	$result = mysql_query($query);
	$num_recruits = mysql_num_rows($result);
	
	$query2 = sprintf("SELECT a.pollid, title, description, a.timestamp, c.full_name AS 'createdby', d.vote, (SELECT COUNT(*) FROM votes b WHERE a.pollid = b.pollid AND vote = 0) AS 'no', (SELECT COUNT(*) FROM votes b WHERE a.pollid = b.pollid AND vote = 1) AS 'yes' 
		FROM polls a 
		INNER JOIN users c ON c.id = a.createdbyid
		LEFT JOIN votes d ON d.pollid = a.pollid AND d.id = $currentuserid
		WHERE isrecruit = 0 
		ORDER BY title ASC");
	$result2 = mysql_query($query2);
	$num_polls = mysql_num_rows($result2);
	
	$query3 = sprintf("SELECT COUNT(*) FROM users WHERE canvote = 1 AND banned = 0 AND approved = 1");
	$result3 = mysql_query($query3);
	while ($row3 = mysql_fetch_assoc($result3)) {
		$votetotal = (int)$row3['COUNT(*)'];
	}

	$query4 = sprintf("SELECT canvote FROM users WHERE id = $currentuserid");
	$result4 = mysql_query($query4);
	while ($row4 = mysql_fetch_assoc($result4)) {
		$canvote = (int)$row4['canvote'];
	}
	
	include 'base.php';
?>

<?php startblock('title') ?>
	Polls
<?php endblock() ?>

<?php startblock('header') ?>
	Polls
<?php endblock() ?>

<?php startblock('content') ?>
	<a href="/createpoll.php" data-role="button" data-icon="plus" data-theme="b">Create Poll</a>		
	<ul data-role="listview" data-inset="true" data-theme="a">
		<li data-role="list-divider">New Recruit Polls</li>
		<?php 
			if ($num_recruits > 0) { 
				while ($row = mysql_fetch_assoc($result)) {
					$pollid = $row['pollid'];
					$title = $row['title'];
					$description = $row['description'];
					$yes = (int)$row['yes'];
					$no = (int)$row['no'];			
					$timestamp = date("F j, Y - g:i a", strtotime($row['timestamp']));
					$createdby = $row['createdby'];
					$vote = $row['vote'];
					
					$yespercent = round(($yes / $votetotal) * 100);
					$nopercent = round(($no / $votetotal) * 100);
					$percentincrease = round((1 / $votetotal) * 100);
					$noclickpercent = $nopercent + $percentincrease;
					$yesclickpercent = $yespercent + $percentincrease;
					
					if ($vote == "") {
						$datatheme = "a";
						$nodisabled = "";
						$yesdisabled = "";
						$notheme = "a";
						$yestheme = "a";
					} else if ($vote == 1) {
						$datatheme = "c";
						$nodisabled = "";
						$yesdisabled = "disabled";
						$notheme = "a";
						$yestheme = "b";
					} else if ($vote == 0) {
						$datatheme = "c";
						$nodisabled = "disabled";
						$yesdisabled = "";
						$notheme = "b";
						$yestheme = "a";
					}
					
					echo "<li id='$pollid' class='pollcontent' data-theme='$datatheme'><h3>$title</h3><p>";
					echo "Added on $timestamp by $createdby<br /><br />$description<hr />"; 
					
					echo "<input id='vote-$pollid' type='hidden' value='$vote' />";
					echo "<input id='increase-$pollid' type='hidden' value='$percentincrease' />";
					echo "<input id='yespercent-$pollid' type='hidden' value='$yespercent' />";
					echo "<input id='nopercent-$pollid' type='hidden' value='$nopercent' />";
					
					if ($canvote == 1) {
						echo "<div data-role='controlgroup' data-type='horizontal' class='pollbuttons'>";
						echo "<button $yesdisabled class='vote-yes' data-inline='true' data-icon='arrow-u' data-mini='true' data-theme='b' rel='$yesclickpercent' data-href='/ajax/voteyes.php?pollid=$pollid'>Yes</button>";
						echo "<button $nodisabled class='vote-no' data-inline='true' data-icon='arrow-d' data-mini='true' data-theme='b' rel='$noclickpercent' data-href='/ajax/voteno.php?pollid=$pollid'>No</button>";
						echo "</div>";
					}
					
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
	
	<ul data-role="listview" data-inset="true" data-theme="a">
		<li data-role="list-divider">Private Guild Polls</li>
		<?php 
			if ($num_polls > 0) { 
				while ($row2 = mysql_fetch_assoc($result2)) {
					$pollid = $row2['pollid'];
					$title = $row2['title'];
					$description = $row2['description'];
					$yes = (int)$row2['yes'];
					$no = (int)$row2['no'];			
					$timestamp = date("F j, Y \a\\t g:ia", strtotime($row2['timestamp']));
					$createdby = $row2['createdby'];
					$vote = $row2['vote'];
					
					$yespercent = round(($yes / $votetotal) * 100);
					$nopercent = round(($no / $votetotal) * 100);
					$percentincrease = round((1 / $votetotal) * 100);
					$noclickpercent = $nopercent + $percentincrease;
					$yesclickpercent = $yespercent + $percentincrease;
					
					if ($vote == "") {
						$datatheme = "a";
						$nodisabled = "";
						$yesdisabled = "";
						$notheme = "a";
						$yestheme = "a";
					} else if ($vote == 1) {
						$datatheme = "c";
						$nodisabled = "";
						$yesdisabled = "disabled";
						$notheme = "a";
						$yestheme = "b";
					} else if ($vote == 0) {
						$datatheme = "c";
						$nodisabled = "disabled";
						$yesdisabled = "";
						$notheme = "b";
						$yestheme = "a";
					}
					
					echo "<li id='$pollid' class='pollcontent' data-theme='$datatheme'><h3>$title</h3><p>";
					echo "Added on $timestamp by $createdby<br /><br />$description<hr />";
					
					echo "<input id='vote-$pollid' type='hidden' value='$vote' />";
					echo "<input id='increase-$pollid' type='hidden' value='$percentincrease' />";
					echo "<input id='yespercent-$pollid' type='hidden' value='$yespercent' />";
					echo "<input id='nopercent-$pollid' type='hidden' value='$nopercent' />";
					
					if ($canvote == 1) {
						echo "<div data-role='controlgroup' data-type='horizontal' class='pollbuttons'>";
						echo "<button $yesdisabled class='vote-yes' data-inline='true' data-icon='arrow-u' data-mini='true' data-theme='b' rel='$yesclickpercent' data-href='/ajax/voteyes.php?pollid=$pollid'>Yes</button>";
						echo "<button $nodisabled class='vote-no' data-inline='true' data-icon='arrow-d' data-mini='true' data-theme='b' rel='$noclickpercent' data-href='/ajax/voteno.php?pollid=$pollid'>No</button>";
						echo "</div>";
					}
					
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
				echo "<li data-theme='c'>There are no private guild polls at this time.</li>";
			}
		?>	
	</ul>
<?php endblock() ?>