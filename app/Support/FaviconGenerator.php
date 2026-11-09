<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class FaviconGenerator
{
    public const SIZE = 32;

    public static function generateFromLogo(?string $logoPath): bool
    {
        if (!$logoPath) {
            return false;
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($logoPath)) {
            return false;
        }

        $sourcePath = $disk->path($logoPath);
        $contents = @file_get_contents($sourcePath);

        if ($contents === false) {
            return false;
        }

        $source = @imagecreatefromstring($contents);

        if (!$source) {
            // SVG and unsupported formats cannot be processed by GD.
            return false;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropSize = min($sourceWidth, $sourceHeight);
        $sourceX = (int) (($sourceWidth - $cropSize) / 2);
        $sourceY = (int) (($sourceHeight - $cropSize) / 2);

        $canvas = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, self::SIZE, self::SIZE, $transparent);
        imagealphablending($canvas, true);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            self::SIZE,
            self::SIZE,
            $cropSize,
            $cropSize
        );

        $pngPath = public_path('favicon.png');
        $pngSaved = imagepng($canvas, $pngPath);

        imagedestroy($canvas);
        imagedestroy($source);

        if (!$pngSaved) {
            return false;
        }

        $pngContents = @file_get_contents($pngPath);

        if ($pngContents === false) {
            return false;
        }

        $ico = pack('vvv', 0, 1, 1)
            . pack('CCCCvvVV', self::SIZE, self::SIZE, 0, 0, 1, 32, strlen($pngContents), 22)
            . $pngContents;

        return file_put_contents(public_path('favicon.ico'), $ico) !== false;
    }
}
