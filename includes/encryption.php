<?php
// Adjust the path to your actual key.php file
require_once 'YOUR-KEY-WILL-GO-HERE';

function encrypt($data, $keyOverride = null) {
    global $key;
    $keyToUse = $keyOverride ?? $key;

    $cipher = "AES-128-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext = openssl_encrypt($data, $cipher, $keyToUse, 0, $iv);
    return base64_encode($iv . $ciphertext);
}

function decrypt($data, $keyOverride = null) {
    global $key;
    $keyToUse = $keyOverride ?? $key;

    if (!$data || !$keyToUse) return '';

    $cipher = "AES-128-CBC";
    $data = base64_decode($data);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $ciphertext = substr($data, $ivlen);
    return openssl_decrypt($ciphertext, $cipher, $keyToUse, 0, $iv);
}
?>