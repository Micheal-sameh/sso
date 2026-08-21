<?php

// One-off generator for PWA app icons, drawn programmatically with GD to
// match the AvaLogo component (navy circle + blue plus) since there's no
// source raster/vector asset to rasterize from.
$outDir = __DIR__.'/../public/icons';

function drawLogo(int $size, bool $transparent, string $path): void
{
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    imagealphablending($im, true);

    $navy = imagecolorallocate($im, 0x1B, 0x2A, 0x4A);
    $blue = imagecolorallocate($im, 0x3B, 0x82, 0xF6);

    if ($transparent) {
        $bg = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $bg);
        $r = $size * 0.48;
        imagefilledellipse($im, (int) ($size / 2), (int) ($size / 2), (int) ($r * 2), (int) ($r * 2), $navy);
    } else {
        imagefilledrectangle($im, 0, 0, $size, $size, $navy);
    }

    // Plus/cross bars, scaled from the AvaLogo SVG's 100x100 viewBox.
    // Maskable (opaque) variant shrinks the cross inward so it survives the
    // OS's circular/rounded-square safe-zone crop.
    $scale = $size / 100;
    $inset = $transparent ? 0 : $size * 0.08;

    $vx1 = 44 * $scale + $inset * 0.4;
    $vx2 = 56 * $scale - $inset * 0.4;
    $vy1 = 20 * $scale + $inset;
    $vy2 = 80 * $scale - $inset;
    imagefilledrectangle($im, (int) $vx1, (int) $vy1, (int) $vx2, (int) $vy2, $blue);

    $hx1 = 20 * $scale + $inset;
    $hx2 = 80 * $scale - $inset;
    $hy1 = 44 * $scale + $inset * 0.4;
    $hy2 = 56 * $scale - $inset * 0.4;
    imagefilledrectangle($im, (int) $hx1, (int) $hy1, (int) $hx2, (int) $hy2, $blue);

    imagepng($im, $path);
    imagedestroy($im);
}

drawLogo(192, true, "$outDir/icon-192.png");
drawLogo(512, true, "$outDir/icon-512.png");
drawLogo(512, false, "$outDir/icon-maskable-512.png");
drawLogo(180, false, "$outDir/apple-touch-icon.png");

echo "Icons generated in $outDir\n";
