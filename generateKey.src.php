<?php

$cadenaServer = $_SERVER["SERVER_ADDR"]."::".$_SERVER["HTTP_HOST"]."::".$_SERVER["SERVER_ADMIN"];
$escapedPW = md5("PuPz:=mbcwH6N@qB|P*4");
$salt = crypt('ceratosystems', '$2y$10$'.$escapedPW.'$');
$saltedPW =  $salt."::".$cadenaServer;

$hashedPW = hash('sha256', $saltedPW);

$fp = fopen('security/initialhash.key','w+');
fwrite($fp, $hashedPW);
fclose($fp);

?>