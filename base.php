<?php 
	require_once 'ti.php';
?>
<!DOCTYPE html> 
<html> 
	<head> 
	<title><?php startblock('title') ?><?php endblock() ?> - Infected Mushrooms Portal</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="icon" href="/images/mushroom.png" />
	<link rel="shortcut icon" href="http://immobile.frodesigns.com/favicon.ico" />
	<link rel="apple-touch-icon" href="/images/mushroom.png"/>
	<link rel="apple-touch-icon-precomposed" href="/images/mushroom.png"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="stylesheet" type="text/css" href="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.css" />
	<link rel="stylesheet" type="text/css" href="/css/style.css" />
	<!--<script type="text/javascript" charset="utf-8" src="/js/cordova-1.5.0.js"></script>-->
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="/js/overrides.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.js"></script>
	<!--<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>-->
	<script type="text/javascript" src="/js/main.js"></script>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-9495256-3']);
		_gaq.push(['_trackPageview']);
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
</head> 
<body> 

<div data-role="page">
	<div data-role="header">
		<a href="/index.php" data-icon="home" data-iconpos="notext">Dashboard</a>		
		<h1><?php startblock('header') ?><?php endblock() ?> - Infected Mushrooms Portal</h1>		
		<?php if (isset($_SESSION['user_id'])) { ?><a href="/logout.php" data-ajax="false">Logout</a><?php } ?>
		<div data-role="navbar">
			<ul>
				<li><a href="/tracker.php">Item Tracker</a></li>
				<li><a href="/forum.php">Forum</a></li>
				<li><a href="/messages.php">Messages</a></li>
				<li><a href="/polls.php">Polls</a></li>
				<li><a href="/members.php">Members</a></li>
			</ul>
		</div>
	</div><!-- /header -->

	<div data-role="content">	
		<?php startblock('content') ?><?php endblock() ?>
	</div><!-- /content -->
	
	<div data-role="footer" data-position="fixed">
		<div class="mushroom"></div>
		<a class="scroll-top" href="#" data-role="button" data-icon="arrow-u">Top</a>
		<a class="refresh" href="#" data-role="button" data-icon="refresh">Refresh</a>		
		<a href="http://www.frodesigns.com/im/index.php?mobile=no" data-rel="external" data-role="button" data-icon="grid">Full Site</a>
	</div>
</div><!-- /page -->

</body>
</html>