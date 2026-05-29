<?php

declare(strict_types=1);

namespace Inane\IdForge\Tests;

use Inane\IdForge\Config\Characters;
use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Encoder\Base64Encoder;
use Inane\IdForge\Generator\UUIDGenerator;
use PHPUnit\Framework\TestCase;

final class UUIDGeneratorTest extends TestCase {
    public function testGenerateProducesValidUuidV4(): void {
        $gen = new UUIDGenerator();
        $uuid = $gen->generate();

        self::assertIsString($uuid);
        self::assertSame(1, preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid));
        self::assertTrue($gen->isValid($uuid));
    }

    public function testIsValidRejectsInvalidStrings(): void {
        $gen = new UUIDGenerator();
        self::assertFalse($gen->isValid('not-a-uuid'));
        self::assertFalse($gen->isValid('12345678-1234-1234-1234-1234567890zz'));
    }

    public function testBase64RoundTrip(): void {
        $gen = new UUIDGenerator();
        $b64 = new Base64Encoder(new EncoderConfig(Characters::base64()));

        $uuid = $gen->generate();
        $encoded = $gen->toBase64($uuid, $b64);

        // URL-safe and no padding
        self::assertSame(0, preg_match('/[+=\/]/', $encoded));

        $decoded = $gen->fromBase64($encoded, $b64);
        self::assertSame($uuid, $decoded);
    }
}
