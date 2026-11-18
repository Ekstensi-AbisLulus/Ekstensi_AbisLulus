<?php

namespace Tests\Browser\Concerns;

use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Membuat file uji nyata di disk untuk $browser->attach().
 *
 * Storage::fake() tidak bisa dipakai di Dusk karena browser dan server
 * aplikasi berjalan di proses terpisah, jadi file harus benar-benar ada.
 * File disimpan di storage/framework/testing/dusk-uploads dan dihapus
 * otomatis setelah setiap test (hook tearDownMakesUploadFiles dipanggil
 * oleh Laravel lewat setUpTraits()).
 */
trait MakesUploadFiles
{
    /** @var list<string> */
    private array $duskUploadFiles = [];

    /**
     * JPEG 8x8 valid, dipakai bila GD di mesin tidak mendukung JPEG.
     */
    private const FALLBACK_JPEG = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCAAIAAgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwDyqiiiv7QPw8//2Q==';

    /**
     * Gambar valid (png, jpg, jpeg, gif, webp) berukuran kira-kira $kb kilobyte.
     *
     * @return string path absolut file
     */
    protected function imageFile(string $ext = 'png', int $kb = 50): string
    {
        $ext = strtolower($ext);
        $bytes = $this->renderImage($ext);

        if (strlen($bytes) < $kb * 1024) {
            $bytes = $this->padImage($bytes, $ext, $kb * 1024);
        }

        return $this->writeDuskUpload('gambar', $ext, $bytes);
    }

    /**
     * Gambar valid yang ukurannya sedikit di atas $mb megabyte
     * (mis. largeImageFile(2) → ±2,1 MB, melewati batas "≤ 2 MB").
     *
     * @return string path absolut file
     */
    protected function largeImageFile(int $mb, string $ext = 'png'): string
    {
        $ext = strtolower($ext);
        $target = $mb * 1024 * 1024 + 100 * 1024;
        $bytes = $this->padImage($this->renderImage($ext), $ext, $target);

        return $this->writeDuskUpload('gambar-besar', $ext, $bytes);
    }

    /**
     * File yang bukan gambar (default PDF). Ekstensi lain berisi teks biasa,
     * sehingga nonImageFile('jpg') menghasilkan file .jpg palsu.
     *
     * @return string path absolut file
     */
    protected function nonImageFile(string $ext = 'pdf'): string
    {
        $ext = strtolower($ext);

        $bytes = $ext === 'pdf'
            ? "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
                ."2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
                ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\n"
                ."trailer<</Root 1 0 R>>\n%%EOF\n"
            : "Ini bukan file gambar. Dibuat untuk pengujian Dusk AbisLulus.\n";

        return $this->writeDuskUpload('bukan-gambar', $ext, $bytes);
    }

    /**
     * Dipanggil otomatis oleh Laravel saat tearDown.
     */
    protected function tearDownMakesUploadFiles(): void
    {
        foreach ($this->duskUploadFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->duskUploadFiles = [];
    }

    private function duskUploadDirectory(): string
    {
        $dir = storage_path('framework/testing/dusk-uploads');

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    private function writeDuskUpload(string $label, string $ext, string $bytes): string
    {
        $path = $this->duskUploadDirectory().DIRECTORY_SEPARATOR
            .$label.'-'.Str::lower(Str::random(8)).'.'.$ext;

        file_put_contents($path, $bytes);
        $this->duskUploadFiles[] = $path;

        return $path;
    }

    private function renderImage(string $ext): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi PHP GD wajib aktif untuk membuat gambar uji.');
        }

        $writer = match ($ext) {
            'png' => 'imagepng',
            'jpg', 'jpeg' => 'imagejpeg',
            'gif' => 'imagegif',
            'webp' => 'imagewebp',
            default => throw new InvalidArgumentException("Ekstensi gambar tidak didukung: {$ext}"),
        };

        $supported = function_exists($writer) && match ($ext) {
            'png' => (imagetypes() & IMG_PNG) !== 0,
            'jpg', 'jpeg' => (imagetypes() & IMG_JPG) !== 0,
            'gif' => (imagetypes() & IMG_GIF) !== 0,
            'webp' => (imagetypes() & IMG_WEBP) !== 0,
        };

        if (! $supported) {
            if (in_array($ext, ['jpg', 'jpeg'], true)) {
                return base64_decode(self::FALLBACK_JPEG);
            }

            throw new RuntimeException("GD di mesin ini tidak mendukung format {$ext}.");
        }

        $image = imagecreatetruecolor(320, 200);
        imagefill($image, 0, 0, imagecolorallocate($image, 37, 99, 235));
        imagefilledrectangle($image, 40, 40, 280, 160, imagecolorallocate($image, 255, 255, 255));
        imagestring($image, 5, 100, 92, 'AbisLulus', imagecolorallocate($image, 37, 99, 235));

        ob_start();
        $writer($image);
        $bytes = (string) ob_get_clean();
        unset($image);

        return $bytes;
    }

    /**
     * Membesarkan file dengan blok metadata yang sah menurut format masing-masing,
     * sehingga file tetap terbaca sebagai gambar valid.
     */
    private function padImage(string $bytes, string $ext, int $targetBytes): string
    {
        $missing = $targetBytes - strlen($bytes);

        if ($missing <= 0) {
            return $bytes;
        }

        switch ($ext) {
            case 'png':
                // Chunk tEXt sebelum IEND (12 byte terakhir).
                $data = "Comment\0".str_repeat('x', max(0, $missing - 20));
                $chunk = pack('N', strlen($data)).'tEXt'.$data.pack('N', crc32('tEXt'.$data));

                return substr($bytes, 0, -12).$chunk.substr($bytes, -12);

            case 'jpg':
            case 'jpeg':
                // Segmen COM (maks 65533 byte data) tepat setelah SOI.
                $segments = '';
                while ($missing > 0) {
                    $len = min(65533, max(1, $missing - 4));
                    $segments .= "\xFF\xFE".pack('n', $len + 2).str_repeat('x', $len);
                    $missing -= $len + 4;
                }

                return substr($bytes, 0, 2).$segments.substr($bytes, 2);

            case 'gif':
                // Comment extension sebelum trailer (0x3B).
                $blocks = '';
                while ($missing > 0) {
                    $len = min(255, max(1, $missing - 1));
                    $blocks .= chr($len).str_repeat('x', $len);
                    $missing -= $len + 1;
                }

                return substr($bytes, 0, -1)."\x21\xFE".$blocks."\x00".substr($bytes, -1);

            default:
                throw new RuntimeException("Tidak bisa memperbesar file {$ext}; gunakan png/jpg/gif.");
        }
    }
}
