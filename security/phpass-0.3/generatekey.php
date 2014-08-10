<?php
require 'PasswordHash.php';

$t_hasher = new PasswordHash(8, FALSE);
$correct = 'salkdasdas6d5asd';
$hash = $t_hasher->HashPassword($correct);
echo $hash;