<?php

if (!getenv('APP_ENV')) {
	putenv('APP_ENV=prod');
	$_ENV['APP_ENV'] = 'prod';
	$_SERVER['APP_ENV'] = 'prod';
}

if (!getenv('APP_DEBUG')) {
	putenv('APP_DEBUG=0');
	$_ENV['APP_DEBUG'] = '0';
	$_SERVER['APP_DEBUG'] = '0';
}

if (!getenv('APP_SECRET')) {
	$defaultSecret = 'change-this-in-vercel-env';
	putenv('APP_SECRET=' . $defaultSecret);
	$_ENV['APP_SECRET'] = $defaultSecret;
	$_SERVER['APP_SECRET'] = $defaultSecret;
}

$secretKeyPath = getenv('JWT_SECRET_KEY') ?: '';
$publicKeyPath = getenv('JWT_PUBLIC_KEY') ?: '';

$hasJwtKeyFiles = $secretKeyPath !== ''
	&& $publicKeyPath !== ''
	&& is_file($secretKeyPath)
	&& is_file($publicKeyPath);

if (!$hasJwtKeyFiles) {
	$jwtDir = sys_get_temp_dir() . '/jwt';

	if (!is_dir($jwtDir)) {
		mkdir($jwtDir, 0755, true);
	}

	$privatePath = $jwtDir . '/private.pem';
	$publicPath = $jwtDir . '/public.pem';
	$passphrase = getenv('JWT_PASSPHRASE');
	if ($passphrase === false) {
		$passphrase = '';
		putenv('JWT_PASSPHRASE=');
		$_ENV['JWT_PASSPHRASE'] = '';
		$_SERVER['JWT_PASSPHRASE'] = '';
	}

	if (!is_file($privatePath) || !is_file($publicPath)) {
		$resource = openssl_pkey_new([
			'digest_alg' => 'sha256',
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);

		if ($resource === false) {
			http_response_code(500);
			echo 'Failed to generate JWT key pair.';
			exit;
		}

		if ($passphrase !== '') {
			openssl_pkey_export($resource, $privateKey, $passphrase);
		} else {
			openssl_pkey_export($resource, $privateKey);
		}

		$keyDetails = openssl_pkey_get_details($resource);
		$publicKey = $keyDetails['key'] ?? null;

		if (!$publicKey) {
			http_response_code(500);
			echo 'Failed to derive JWT public key.';
			exit;
		}

		file_put_contents($privatePath, $privateKey);
		file_put_contents($publicPath, $publicKey);
	}

	putenv('JWT_SECRET_KEY=' . $privatePath);
	$_ENV['JWT_SECRET_KEY'] = $privatePath;
	$_SERVER['JWT_SECRET_KEY'] = $privatePath;

	putenv('JWT_PUBLIC_KEY=' . $publicPath);
	$_ENV['JWT_PUBLIC_KEY'] = $publicPath;
	$_SERVER['JWT_PUBLIC_KEY'] = $publicPath;
}

return require __DIR__ . '/../public/index.php';