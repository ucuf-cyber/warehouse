<?php

// One-off generator for the PWA manifest icons — a rounded blue square with
// a white "B", matching the sidebar brand mark. Run with: php scripts/generate-pwa-icons.php

$sizes = [192, 512];
$outDir = __DIR__.'/../public/icons';
$font = getenv('SYSTEMROOT') ? getenv('SYSTEMROOT').'/Fonts/arialbd.ttf' : 'C:/Windows/Fonts/arialbd.ttf';

if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

if (! is_file($font)) {
    fwrite(STDERR, "Font not found at {$font}\n");
    exit(1);
}

foreach ($sizes as $size) {
    $image = imagecreatetruecolor($size, $size);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    $blue = imagecolorallocate($image, 0x2F, 0x6F, 0xED);
    $radius = (int) round($size * 0.22);

    imagefilledrectangle($image, $radius, 0, $size - $radius - 1, $size - 1, $blue);
    imagefilledrectangle($image, 0, $radius, $size - 1, $size - $radius - 1, $blue);
    imagefilledellipse($image, $radius, $radius, $radius * 2, $radius * 2, $blue);
    imagefilledellipse($image, $size - $radius - 1, $radius, $radius * 2, $radius * 2, $blue);
    imagefilledellipse($image, $radius, $size - $radius - 1, $radius * 2, $radius * 2, $blue);
    imagefilledellipse($image, $size - $radius - 1, $size - $radius - 1, $radius * 2, $radius * 2, $blue);

    $white = imagecolorallocate($image, 255, 255, 255);
    $fontSize = $size * 0.52;

    $box = imagettfbbox($fontSize, 0, $font, 'B');
    $textWidth = abs($box[4] - $box[0]);
    $textHeight = abs($box[5] - $box[1]);
    $x = (int) (($size - $textWidth) / 2);
    $y = (int) (($size + $textHeight) / 2);

    imagettftext($image, $fontSize, 0, $x, $y, $white, $font, 'B');

    imagepng($image, "{$outDir}/icon-{$size}.png");
    imagedestroy($image);

    echo "Generated icon-{$size}.png\n";
}
