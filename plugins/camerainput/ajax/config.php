<?php

$AJAX_INCLUDE = 1;

include ('../../../inc/includes.php');

header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   http_response_code(405);
   die();
}

$config = Config::getConfigurationValues('plugin:camerainput');
if (isset($config['barcode_formats'])) {
   $config['barcode_formats'] = importArrayFromDB($config['barcode_formats']);
}
echo json_encode($config);
