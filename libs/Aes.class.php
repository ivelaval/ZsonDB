<?php

// Datos de entrada
$texto = 'cerato';
$key   = '12345678901234567890123456789012';

// Proceso de cifrado
$iv    = 'abcdefghijklmnopqrstuvwxyz012345';
$td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
mcrypt_generic_init($td, $key, $iv);
$texto_cifrado = mcrypt_generic($td, $texto);
mcrypt_generic_deinit($td);
mcrypt_module_close($td);

// Opcionalmente codificamos en base64
$texto_cifrado = base64_encode($texto_cifrado);

echo "$texto_cifrado\n";



// Opcionalmente descodificamos en base64
$texto_cifrado = base64_decode($texto_cifrado);

// Proceso de descifrado
$td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
mcrypt_generic_init($td, $key, $iv);
$texto = mdecrypt_generic($td, $texto_cifrado);
$texto = trim($texto, "\0");

echo "$texto\n";

?>