<?php  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="en" />
		<title>x-objects event log</title>
		<LINK 
			REL="stylesheet" 
			HREF="<?php echo "/css/html5reset.css" ?>" 
			TYPE="text/css" 
			TITLE="24-bit Color Style" 
			MEDIA="screen, print">
		<LINK 
			REL="stylesheet" 
			HREF="<?php echo "/css/x-objects.css" ?>" 
			TYPE="text/css" 
			TITLE="24-bit Color Style" 
			MEDIA="screen, print">
		<LINK 
			REL="stylesheet" 
			HREF="<?php echo "/css/xevents.css" ?>" 
			TYPE="text/css" 
			TITLE="24-bit Color Style" 
			MEDIA="screen, print">
		<script language="javascript" type="text/javascript" src="<?php echo "/js/jquery-1.6.3.js"; ?>"></script>
		<script type="text/javascript" src="<?php echo "/js/jheartbeat.js"; ?>"></script>
		<script type="text/javascript" src="<?php echo "/js/xo-base.js"; ?>"></script>
		<script type="text/javascript" src="<?php echo "/js/xevents.js"; ?>"></script>
		
			<script>
				$(function(){
					$("div.event-log").xevents( { "use_rest" : true });
				});
			</script>
    
	</head>
	<body>
		<div class="container">
			<div class="event-log-controls xo-round3">
				<button class="empty-log">Clear Event Log</button>
			</div>
			<div class="event-log xo-round3">Please wait while the event log initializes</div>
		</div>
	</body>
</html>
		
    
