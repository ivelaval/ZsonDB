<?php

$escapedPW = md5("PuPz:=mbcwH6N@qB|P*4");
$salt = crypt('ceratosystems', '$2y$10$'.$escapedPW.'$');
$saltedPW =  $escapedPW."::".$salt;
$hashedPW = hash('sha256', $saltedPW);

$fp = fopen('security/finalhash.key','w+');
fwrite($fp, $hashedPW);
fclose($fp);

?>