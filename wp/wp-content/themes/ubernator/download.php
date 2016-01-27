<?php

include realpath('../../../wp-load.php');

$filename = realpath('../../../'.$_SERVER['REQUEST_URI']);

if (is_user_logged_in()) {
    $info = getimagesize($filename);
    $file = fopen($filename, 'r');
    header('Cache-Control: max-age=86400');
    header('Content-type: '.$info['mime']);
    fpassthru($file);
    exit;
}

header('HTTP/1.0 403 Forbidden');
exit;
