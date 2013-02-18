<?php
global $container,$view_key,$xobjects_location,$webapp_location,$controller_name,$page_vars;
    $login_url = $container->config->login_url;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" xmlns:fb="http://www.facebook.com/2008/fbml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <meta name="description" content="X-Objects default html5 template v1.0"/>
    <meta name="keywords" content=""/>
    <link rel="stylesheet" type="text/css" href="/css/html5reset.css" />
    <link rel="stylesheet" type="text/css" href="/css/default.css" />
    <?php if ( file_exists($webapp_location."/css/$view_key.css")) { ?>
    <link rel="stylesheet" type="text/css" href="/css/<?php echo $view_key;?>.css" />
    <?php } ?>
    <link rel="stylesheet" type="text/css" href="/css/x-objects.css" />
    <!-- jquery and javascript framework -->
    <script type="text/javascript" src="/js/settings.js"></script>
    <script type="text/javascript" src="/js/script.js"></script>
    <?php if ( file_exists($webapp_location."/js/$view_key.js")) { ?>
    <script type="text/javascript" src="/js/<?php echo $view_key?>.js"></script>
    <?php } ?>
    <title>My New X-Objects Application</title>
    <script>
    </script>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <div class="content">
            <?php
            $f = $webapp_location . "/app/views/pages/$view_key.php";
            if ( file_exists( $f ))
                require_once( $f);
            else
                echo "Oops!  the view $view_key does not exists, or could not be found...";
            ?>
            </div>
        </div>
    </div>
</body>
</html>
