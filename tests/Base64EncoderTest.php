<?php

declare(strict_types=1);

namespace Inane\IdForge\Tests;

use Inane\IdForge\Config\Characters;
use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Encoder\Base64Encoder;
use Inane\Stdlib\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class Base64EncoderTest extends TestCase {
    public function testEncodeDecodeRoundTrip(): void {
        $encoder = new Base64Encoder(new EncoderConfig(Characters::base64()));
        $data = "\x00\x01Hello, World!\x7F"; // binary-safe payload

        $encoded = $encoder->encode($data);
        self::assertIsString($encoded);

        $decoded = $encoder->decode($encoded);
        self::assertSame($data, $decoded);
    }

    public function testUrlEncodeProducesUrlSafeCharactersOnly(): void {
        $encoder = new Base64Encoder(new EncoderConfig(Characters::base64()));
        $data = random_bytes(16);

        $url = $encoder->urlEncode($data);

        // Ensure URL-safe (no '+', '/', '=')
        self::assertSame(0, preg_match('/[+=\/]/', $url), 'URL-safe Base64 contains invalid characters');

        // And it must decode back to original
        $decoded = $encoder->urlDecode($url);
        self::assertSame($data, $decoded);
    }

    public function testDecodeThrowsOnInvalidBase64(): void {
        $this->expectException(InvalidArgumentException::class);
        (new Base64Encoder(new EncoderConfig(Characters::base64())))->decode('!!notbase64!!');
    }

    public function testUrlDecodeThrowsOnInvalidBase64(): void {
        $this->expectException(InvalidArgumentException::class);
        // invalid even after padding normalization
        (new Base64Encoder(new EncoderConfig(Characters::base64())))->urlDecode('**__invalid__**');
    }
}
