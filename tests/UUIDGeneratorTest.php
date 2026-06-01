<?php

/**
 * Inane: IdForge
 *
 * Inane Encoder & ID Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab<peep@inane.co.za>
 * @package  inanepain\id-forge
 * @category id-forge
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\IdForge\Tests;

use Inane\IdForge\{
    Config\Characters,
    Config\EncoderConfig,
    Encoder\Base64Encoder,
    Generator\UUIDGenerator};
use Inane\Stdlib\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

/**
 * Class UUIDGeneratorTest
 *
 * Unit tests for the `UUIDGenerator` including format validation and
 * Base64 round-trip encoding/decoding using `Base64Encoder`.
 */
final class UUIDGeneratorTest extends TestCase {
    /**
     * Ensures `UUIDGenerator::generate` produces a valid RFC 4122 version 4 UUID.
     *
     * @return void
     * @throws RandomException
     */
    public function testGenerateProducesValidUuidV4(): void {
        // Arrange: create a generator instance
        $gen = new UUIDGenerator();
        // Act: generate a UUID v4 string
        $uuid = $gen->generate();

        // Assert: string type, v4 pattern match and validator acceptance
        self::assertIsString($uuid);
        self::assertSame(1, preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid));
        self::assertTrue($gen->isValid($uuid));
    }

    /**
     * Verifies `UUIDGenerator::isValid` rejects non-UUID or malformed UUID strings.
     *
     * @return void
     */
    public function testIsValidRejectsInvalidStrings(): void {
        $gen = new UUIDGenerator();
        self::assertFalse($gen->isValid('not-a-uuid'));
        self::assertFalse($gen->isValid('12345678-1234-1234-1234-1234567890zz'));
    }

    /**
     * Tests Base64 encode/decode a round-trip of a UUID without padding and using URL-safe characters.
     *
     * @return void
     * @throws RandomException
     * @throws InvalidArgumentException
     */
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
