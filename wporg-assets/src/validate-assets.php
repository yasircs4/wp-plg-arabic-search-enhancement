<?php

$dist_dir = dirname(__DIR__) . '/dist';

$expected = [
	'banner-772x250.png' => [772, 250, 4 * 1024 * 1024],
	'banner-1544x500.png' => [1544, 500, 4 * 1024 * 1024],
	'icon-128x128.png' => [128, 128, 1024 * 1024],
	'icon-256x256.png' => [256, 256, 1024 * 1024],
	'screenshot-1.png' => [1440, 900, 10 * 1024 * 1024],
	'screenshot-2.png' => [1440, 900, 10 * 1024 * 1024],
	'screenshot-3.png' => [1440, 900, 10 * 1024 * 1024],
	'screenshot-4.png' => [1440, 900, 10 * 1024 * 1024],
];

$errors = [];

foreach ($expected as $file => [$width, $height, $max_bytes]) {
	$path = $dist_dir . '/' . $file;

	if (!is_file($path)) {
		$errors[] = "$file is missing";
		continue;
	}

	$size = getimagesize($path);
	if (!$size) {
		$errors[] = "$file is not a readable image";
		continue;
	}

	if ((int) $size[0] !== $width || (int) $size[1] !== $height) {
		$errors[] = sprintf('%s is %dx%d, expected %dx%d', $file, $size[0], $size[1], $width, $height);
	}

	$bytes = filesize($path);
	if ($bytes > $max_bytes) {
		$errors[] = sprintf('%s is %.2f MB, over limit %.2f MB', $file, $bytes / 1048576, $max_bytes / 1048576);
	}
}

if ($errors) {
	fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
	exit(1);
}

echo 'WordPress.org assets validated.' . PHP_EOL;
