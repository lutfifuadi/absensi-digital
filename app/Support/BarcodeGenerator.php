<?php

namespace App\Support;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeGenerator
{
    /**
     * Helper sanitasi dan penentuan format data barcode.
     * Mengembalikan array dengan format: [string $processedData, string $barcodeType]
     */
    public static function sanitizeAndDetermineType(string $data, string $generatorType = 'svg'): array
    {
        $generator = $generatorType === 'png' ? new BarcodeGeneratorPNG() : new BarcodeGeneratorSVG();

        // Jika data asli mengandung huruf, langsung fallback ke Code 128
        if (preg_match('/[a-zA-Z]/', $data)) {
            return [$data, $generator::TYPE_CODE_128];
        }

        // Bersihkan dari karakter non-numerik
        $cleanData = preg_replace('/[^0-9]/', '', $data);

        if (empty($cleanData)) {
            return [$data, $generator::TYPE_CODE_128];
        }

        // Ambil 8 digit terakhir dari angka numerik yang bersih
        $cleanData = substr($cleanData, -8);
        $length = strlen($cleanData);

        if ($length <= 8) {
            $padded = str_pad($cleanData, 8, '0', STR_PAD_LEFT);
            try {
                // Coba test apakah EAN-8 valid
                // Catatan: BarcodeGenerator EAN mengharuskan checksum digit valid.
                // Jika library picqer melempar InvalidCheckDigitException, kita bisa mencoba menghitung/mengganti digit terakhir atau fallback.
                // Tapi mari kita lihat apakah exception yang dilempar karena checksum digit.
                // Jika input data sudah 8 digit lengkap tetapi checksum digit salah, ia melempar exception.
                // Jika input kurang dari 8 digit (misal '12345'), maka str_pad ke 8 digit menghasilkan '00012345' (panjang 8).
                // Tapi EAN-8 menghitung digit terakhir sebagai checksum.
                // Jika kita coba getBarcode('00012345', EAN_8), checksum digit '5' mungkin tidak valid untuk '0001234'.
                // Oleh karena itu, mari kita tangani pembuatan checksum digit yang benar agar EAN-8/EAN-13 tidak melempar Exception karena checksum.
                // Atau, jika melempar Exception, kita fallback ke Code 128.
                $generator->getBarcode($padded, $generator::TYPE_EAN_8);
                return [$padded, $generator::TYPE_EAN_8];
            } catch (\Exception $e) {
                // Jika gagal karena check digit atau hal lain, mari kita coba hitung checksum digit yang benar jika input datanya < 8 digit,
                // sehingga padding + checksum yang benar bisa lolos EAN.
                // EAN-8 menghitung checksum dari 7 digit pertama.
                if ($length < 8) {
                    $sevenDigits = str_pad($cleanData, 7, '0', STR_PAD_LEFT);
                    try {
                        // Picqer EAN generator menerima 7 digit untuk EAN-8 dan akan mengalkulasi digit ke-8 secara otomatis!
                        // Mari kita coba panggil dengan 7 digit.
                        $generator->getBarcode($sevenDigits, $generator::TYPE_EAN_8);
                        // Jika berhasil, panggil getBarcode dengan 7 digit
                        return [$sevenDigits, $generator::TYPE_EAN_8];
                    } catch (\Exception $ex) {
                        // ignore
                    }
                }
                // Fallback ke Code 128 jika tetap tidak valid untuk EAN-8
                return [$padded, $generator::TYPE_CODE_128];
            }
        }

        return [$padded ?? $cleanData, $generator::TYPE_CODE_128];
    }

    /**
     * Render Barcode as SVG String.
     */
    public static function renderSvg(string $data): string
    {
        $generator = new BarcodeGeneratorSVG();
        [$processedData, $type] = self::sanitizeAndDetermineType($data, 'svg');
        return $generator->getBarcode($processedData, $type);
    }

    /**
     * Render Barcode as Raw PNG bytes.
     */
    public static function renderPng(string $data): string
    {
        $generator = new BarcodeGeneratorPNG();
        [$processedData, $type] = self::sanitizeAndDetermineType($data, 'png');
        return $generator->getBarcode($processedData, $type);
    }

    /**
     * Render Barcode as PNG Base64 Data URI.
     */
    public static function renderPngBase64(string $data): string
    {
        return 'data:image/png;base64,' . base64_encode(self::renderPng($data));
    }

    /**
     * Get the formatted data that is actually encoded in the barcode.
     */
    public static function getFormattedData(string $data): string
    {
        [$processedData] = self::sanitizeAndDetermineType($data, 'svg');
        return $processedData;
    }
}
