<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;

class QrCodeGenerator
{
    public static function generate(string $prefix = null): string
    {
        $uuid = Str::uuid()->toString();

        if ($prefix) {
            return strtoupper($prefix) . '-' . $uuid;
        }

        return $uuid;
    }

    public static function renderDataUri(?string $data, int $size = 220): string
    {
        $payload = trim((string) $data);

        if ($payload === '') {
            $payload = 'MISSING_QR_CODE';
        }

        $builder = new Builder(
            writer: new PngWriter(),
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: $size,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $builder->build();

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
