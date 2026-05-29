<?php

namespace app\helpers;

use Yii;
use yii\web\UploadedFile;

/**
 * Сохранение изображений в @webroot/uploads/...
 */
final class ImageUploadHelper
{
    /** @var string[] */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public const MAX_BYTES = 5_242_880; // 5 MB

    /**
     * @return string|null относительный путь от web (uploads/…)
     */
    public static function saveImage(UploadedFile $file, string $webSubdir, string $namePrefix): ?string
    {
        if ($file->getHasError()) {
            return null;
        }

        $ext = strtolower((string) $file->extension);
        if ($ext === '') {
            $ext = strtolower((string) pathinfo($file->name, PATHINFO_EXTENSION));
        }
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return null;
        }

        if ($file->size > self::MAX_BYTES) {
            return null;
        }

        $dir = Yii::getAlias('@webroot/uploads/' . trim($webSubdir, '/'));
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return null;
        }

        $basename = preg_replace('/[^a-z0-9_-]/i', '', $namePrefix) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $basename;
        if (!$file->saveAs($fullPath, false)) {
            return null;
        }

        return 'uploads/' . trim($webSubdir, '/') . '/' . $basename;
    }

    public static function deleteIfUploaded(?string $webPath): void
    {
        $path = trim((string) $webPath);
        if ($path === '' || !str_starts_with($path, 'uploads/')) {
            return;
        }
        $full = Yii::getAlias('@webroot/' . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/')));
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
