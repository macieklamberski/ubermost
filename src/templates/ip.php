<?php

$ip = $_SERVER['REMOTE_ADDR'];
$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

// echo json_encode(array(
//   'ip' => $details
// ));


// echo 'tet';


var_dump($details);
