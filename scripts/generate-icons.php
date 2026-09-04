<?php

// Regenerates the PWA/app icons in public/icons from the source logo mark at
// public/images/logo.png (a transparent PNG cropped to just the icon glyph).
// Run with: php scripts/generate-icons.php
$logoPath = __DIR__.'/../public/images/logo.png';
$outDir = __DIR__.'/../public/icons';

$navy = [0x1B, 0x2A, 0x4A]; // brand navy, matches AvaLogo's default textColor / theme-color

$icon = imagecreatefrompng($logoPath);
imagealphablending($icon, true);
imagesavealpha($icon, true);

function resizedTransparent($src, int $size)
{
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefilledrectangle($out, 0, 0, $size, $size, $transparent);
    imagealphablending($out, true);
    imagecopyresampled($out, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));

    return $out;
}

function onOpaqueBackground($src, int $size, array $bg, float $iconScale)
{
    $out = imagecreatetruecolor($size, $size);
    $fill = imagecolorallocate($out, $bg[0], $bg[1], $bg[2]);
    imagefilledrectangle($out, 0, 0, $size, $size, $fill);

    $iconSize = (int) round($size * $iconScale);
    $offset = (int) round(($size - $iconSize) / 2);
    imagealphablending($out, true);
    imagecopyresampled($out, $src, $offset, $offset, 0, 0, $iconSize, $iconSize, imagesx($src), imagesy($src));

    return $out;
}

imagepng(resizedTransparent($icon, 192), "$outDir/icon-192.png");
imagepng(resizedTransparent($icon, 512), "$outDir/icon-512.png");
// maskable: opaque bg, icon shrunk to fit the OS's circular/rounded-square safe zone
imagepng(onOpaqueBackground($icon, 512, $navy, 0.62), "$outDir/icon-maskable-512.png");
// apple touch icon: iOS ignores alpha (renders black), so an opaque bg is required
imagepng(onOpaqueBackground($icon, 180, $navy, 0.78), "$outDir/apple-touch-icon.png");

// favicon.ico: single embedded 48x48 PNG, supported by all modern browsers/OSes
$fav = resizedTransparent($icon, 48);
ob_start();
imagepng($fav);
$pngData = ob_get_clean();

$header = pack('vvv', 0, 1, 1); // reserved, type=1 (icon), count=1
$entry = pack('CCCCvvVV', 48, 48, 0, 0, 1, 32, strlen($pngData), 6 + 16);
file_put_contents(__DIR__.'/../public/favicon.ico', $header.$entry.$pngData);

echo "Icons generated in $outDir and public/favicon.ico\n";
