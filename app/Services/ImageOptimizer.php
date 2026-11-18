<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Compress, resize, auto-orient, and store an uploaded image.
     *
     * @param  string  $directory  Storage subdirectory (e.g. 'orders/01HXYZ...')
     * @param  string  $disk  Storage disk ('public' by default)
     * @param  int  $maxWidth  Max width in pixels (default 1600)
     * @param  int  $maxHeight  Max height in pixels (default 1600)
     * @param  int  $quality  Compression quality (1-100, default 80)
     * @return string Stored relative file path
     */
    public static function optimizeAndStore(
        UploadedFile|string $file,
        string $directory,
        string $disk = 'public',
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $quality = 80
    ): string {
        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! $realPath || ! file_exists($realPath)) {
            if ($file instanceof UploadedFile) {
                return $file->store($directory, $disk);
            }

            throw new \InvalidArgumentException("File does not exist: {$realPath}");
        }

        try {
            $imageInfo = @getimagesize($realPath);
            if (! $imageInfo) {
                return $file instanceof UploadedFile ? $file->store($directory, $disk) : '';
            }

            $mime = $imageInfo['mime'] ?? '';
            $source = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
                'image/png' => @imagecreatefrompng($realPath),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($realPath) : null,
                'image/gif' => @imagecreatefromgif($realPath),
                default => null,
            };

            if (! $source) {
                return $file instanceof UploadedFile ? $file->store($directory, $disk) : '';
            }

            // Fix EXIF orientation for JPEGs from smartphone cameras
            if (in_array($mime, ['image/jpeg', 'image/jpg']) && function_exists('exif_read_data')) {
                try {
                    $exif = @exif_read_data($realPath);
                    if (! empty($exif['Orientation'])) {
                        $source = match ((int) $exif['Orientation']) {
                            3 => imagerotate($source, 180, 0),
                            6 => imagerotate($source, -90, 0),
                            8 => imagerotate($source, 90, 0),
                            default => $source,
                        };
                    }
                } catch (\Throwable) {
                    // Ignore EXIF parsing issues
                }
            }

            $origWidth = imagesx($source);
            $origHeight = imagesy($source);

            // Calculate scaled dimensions keeping aspect ratio
            $scale = min($maxWidth / max($origWidth, 1), $maxHeight / max($origHeight, 1), 1.0);
            $targetWidth = max(1, (int) round($origWidth * $scale));
            $targetHeight = max(1, (int) round($origHeight * $scale));

            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

            // Preserve alpha channel transparency for PNG / WebP
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
            imagealphablending($canvas, true);

            imagecopyresampled(
                $canvas,
                $source,
                0, 0, 0, 0,
                $targetWidth,
                $targetHeight,
                $origWidth,
                $origHeight
            );

            $supportsWebp = function_exists('imagewebp');
            $extension = $supportsWebp ? 'webp' : 'jpg';

            ob_start();
            if ($supportsWebp) {
                imagewebp($canvas, null, $quality);
            } else {
                imagejpeg($canvas, null, $quality);
            }
            $stream = ob_get_clean();

            imagedestroy($source);
            imagedestroy($canvas);

            $filename = Str::random(40).'.'.$extension;
            $relativePath = trim($directory, '/').'/'.$filename;

            Storage::disk($disk)->put($relativePath, $stream);

            return $relativePath;
        } catch (\Throwable $e) {
            // Graceful fallback to default file upload
            if ($file instanceof UploadedFile) {
                return $file->store($directory, $disk);
            }

            throw $e;
        }
    }
}
