<?php
$AJAX_INCLUDE = 1;
include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

echo Session::getNewCSRFToken();
