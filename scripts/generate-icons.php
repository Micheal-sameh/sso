<?php

// Regenerates the PWA/app icons in public/icons from the full logo at
// public/images/logo.png (icon + "Avarewase Account" wordmark, background
// removed). The source isn't square, so every output "contain"-fits it onto
// a square canvas instead of stretching it.
// Run with: php scripts/generate-icons.php
$logoPath = __DIR__.'/../public/images/logo.png';
$outDir = __DIR__.'/../public/icons';

$navy = [0x1B, 0x2A, 0x4A]; // brand navy, matches AvaLogo's default textColor / theme-color

$icon = imagecreatefrompng($logoPath);
imagealphablending($icon, true);
imagesavealpha($icon, true);
$srcW = imagesx($icon);
$srcH = imagesy($icon);

function drawContained($dst, $src, int $canvasSize, float $fitScale = 1.0): void
{
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $scale = min($canvasSize / $srcW, $canvasSize / $srcH) * $fitScale;
    $w = (int) round($srcW * $scale);
    $h = (int) round($srcH * $scale);
    $x = (int) round(($canvasSize - $w) / 2);
    $y = (int) round(($canvasSize - $h) / 2);
    imagecopyresampled($dst, $src, $x, $y, 0, 0, $w, $h, $srcW, $srcH);
}

function transparentCanvas(int $size)
{
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefilledrectangle($out, 0, 0, $size, $size, $transparent);
    imagealphablending($out, true);

    return $out;
}

function opaqueCanvas(int $size, array $bg)
{
    $out = imagecreatetruecolor($size, $size);
    $fill = imagecolorallocate($out, $bg[0], $bg[1], $bg[2]);
    imagefilledrectangle($out, 0, 0, $size, $size, $fill);

    return $out;
}

$c = transparentCanvas(192);
drawContained($c, $icon, 192);
imagepng($c, "$outDir/icon-192.png");

$c = transparentCanvas(512);
drawContained($c, $icon, 512);
imagepng($c, "$outDir/icon-512.png");

// maskable: opaque bg, logo shrunk to fit the OS's circular/rounded-square safe zone
$c = opaqueCanvas(512, $navy);
drawContained($c, $icon, 512, 0.72);
imagepng($c, "$outDir/icon-maskable-512.png");

// apple touch icon: iOS ignores alpha (renders black), so an opaque bg is required
$c = opaqueCanvas(180, $navy);
drawContained($c, $icon, 180, 0.82);
imagepng($c, "$outDir/apple-touch-icon.png");

// favicon.ico: single embedded 48x48 PNG, supported by all modern browsers/OSes
$fav = transparentCanvas(48);
drawContained($fav, $icon, 48);
ob_start();
imagepng($fav);
$pngData = ob_get_clean();

$header = pack('vvv', 0, 1, 1); // reserved, type=1 (icon), count=1
$entry = pack('CCCCvvVV', 48, 48, 0, 0, 1, 32, strlen($pngData), 6 + 16);
file_put_contents(__DIR__.'/../public/favicon.ico', $header.$entry.$pngData);

echo "Icons generated in $outDir and public/favicon.ico\n";
