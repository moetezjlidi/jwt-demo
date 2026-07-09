<?php
$config = [
    'digest_alg' => 'sha256',
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

$res = openssl_pkey_new($config);

if (!$res) {
    echo "Error: " . openssl_error_string() . "\n";
    exit(1);
}

openssl_pkey_export($res, $privKey);
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey['key'];

if (!is_dir('config/jwt')) {
    mkdir('config/jwt', 0755, true);
}

file_put_contents('config/jwt/private.pem', $privKey);
file_put_contents('config/jwt/public.pem', $pubKey);

echo "Keys generated successfully!\n";