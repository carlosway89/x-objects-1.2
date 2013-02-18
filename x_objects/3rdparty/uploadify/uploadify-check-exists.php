<?php
/*
Uploadify v3.0.0
Copyright (c) 2010 Ronnie Garcia
*/
require_once('../../include/bootstrap.php');

$folder = RealObjects::instance()->config->upload_folder;

//echo $folder . $_REQUEST['filename'];

if (file_exists($_SERVER['DOCUMENT_ROOT'] . $folder . $_POST['filename'])) {
	echo 1;
} else {
	echo 0;
}
?>