<?php
include ("../../../inc/includes.php");

header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
header('Pragma: private'); /// IE BUG + SSL
header('Cache-control: private, must-revalidate'); /// IE BUG + SSL
header("Content-disposition: attachment; filename=export.svg");
header("Content-type: image/svg+xml");

$svg_content = str_replace('&', '&amp;', Toolbox::stripslashes_deep(html_entity_decode($_REQUEST['svg_content'])));

echo str_replace("<svg ", '<svg version="1.1" baseProfile="full" xmlns="http://www.w3.org/2000/svg" ', $svg_content);
