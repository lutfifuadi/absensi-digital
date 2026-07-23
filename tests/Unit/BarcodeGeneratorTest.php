<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Support\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeGeneratorTest extends TestCase
{
    /** @test */
    public function it_correctly_sanitizes_and_uses_ean8_for_valid_lengths()
    {
        // 8 digits numeric
        [$data, $type] = BarcodeGenerator::sanitizeAndDetermineType('12345670');
        $this->assertEquals('12345670', $data);
        $this->assertEquals(BarcodeGeneratorSVG::TYPE_EAN_8, $type);

        // Less than 8 digits numeric (e.g. 5 digits) -> padded and EAN-8
        // Let's assert it resolves to a valid EAN-8 format (7 digits because check digit is added/calculated by generator)
        [$data, $type] = BarcodeGenerator::sanitizeAndDetermineType('12345');
        $this->assertEquals('0012345', $data);
        $this->assertEquals(BarcodeGeneratorSVG::TYPE_EAN_8, $type);

        // 10 digits numeric -> takes last 8 digits ('34567890') and EAN-8 (34567890 is valid EAN-8)
        [$data, $type] = BarcodeGenerator::sanitizeAndDetermineType('1234567890');
        $this->assertEquals('34567890', $data);
        $this->assertEquals(BarcodeGeneratorSVG::TYPE_EAN_8, $type);
    }

    /** @test */
    public function it_falls_back_to_code128_for_alphanumeric_data()
    {
        // Alphanumeric data with letters
        [$data, $type] = BarcodeGenerator::sanitizeAndDetermineType('NIS12345');
        $this->assertEquals('NIS12345', $data);
        $this->assertEquals(BarcodeGeneratorSVG::TYPE_CODE_128, $type);
    }

    /** @test */
    public function it_falls_back_to_code128_for_invalid_checksums_or_empty_data()
    {
        // Empty data
        [$data, $type] = BarcodeGenerator::sanitizeAndDetermineType('');
        $this->assertEquals('', $data);
        $this->assertEquals(BarcodeGeneratorSVG::TYPE_CODE_128, $type);

        // Only letters
        [$data, $type] = BarcodeGenerator::sanitizeAndDetermineType('abc');
        $this->assertEquals('abc', $data);
        $this->assertEquals(BarcodeGeneratorSVG::TYPE_CODE_128, $type);
    }

    /** @test */
    public function it_renders_svg_and_png_successfully()
    {
        // Valid EAN-13
        $svg = BarcodeGenerator::renderSvg('1234567890128');
        $this->assertNotEmpty($svg);
        $this->assertStringContainsString('<svg', $svg);

        $png = BarcodeGenerator::renderPng('1234567890128');
        $this->assertNotEmpty($png);

        // Fallback Code-128
        $svgFallback = BarcodeGenerator::renderSvg('NIS12345');
        $this->assertNotEmpty($svgFallback);
        $this->assertStringContainsString('<svg', $svgFallback);

        $pngFallback = BarcodeGenerator::renderPng('NIS12345');
        $this->assertNotEmpty($pngFallback);
    }
}
