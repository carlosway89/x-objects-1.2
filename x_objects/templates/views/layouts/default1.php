<?php
    global $view_key,$xobjects_location,$webapp_location,$controller_name,$page_vars;
 ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="en" />
		<meta property="description" content=""/>
    	<link rel="stylesheet" type="text/css" href="/css/html5reset.css" />
		<link rel="stylesheet" type="text/css" href="/css/_appname_.css" />
    	<link rel="stylesheet" type="text/css" href="/css/jquery-ui.css" />
    	<?php if ( file_exists($webapp_location."/css/$view_key.css")) { ?>
		<link rel="stylesheet" type="text/css" href="/css/<?php echo $view_key;?>.css" />
    	<?php } ?>
		<link rel="stylesheet" type="text/css" href="/css/x-objects.css" />
		<!-- jquery and javascript framework -->
		<script type="text/javascript" src="/js/settings.js"></script>
		<script type="text/javascript" src="/js/script.js"></script>
		<script type="text/javascript" src="/js/_appname_.js"></script>
		<?php if ( file_exists($webapp_location."/js/$view_key.js")) { ?>
		<script type="text/javascript" src="/js/<?php echo $view_key?>.js"></script>
    	<?php } ?>
		<title>Welcome to _appname_</title>
		<script>
			_appname_.init('<?php echo $view_key; ?>');
		</script>
  	</head>
 	<body>
 		<div class="container">
 		<?php
 			$f = $webapp_location . "/app/views/pages/$view_key.php";
 			if ( file_exists( $f ))
 				require_once( $f);
 			else 
 				echo "Oops!  the view $view_key does not exists, or could not be found...";	
 				 ?>
		</div>
	</body>
</html>
