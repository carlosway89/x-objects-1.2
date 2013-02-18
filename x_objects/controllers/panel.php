<?php
// get the usage_manager
$usage = $container->usage_manager;
$bypass_view = true;
echo $usage->debug;
echo $usage->log_enabled;
echo $usage->num_events;
?>