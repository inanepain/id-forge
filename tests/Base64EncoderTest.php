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

use Inane\IdForge\Config\{
    Characters,
    EncoderConfig};
use Inane\IdForge\Encoder\Base64Encoder;
use Inane\Stdlib\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

use function preg_match;
use function random_bytes;

/**
 * Class Base64EncoderTest
 *
 * Unit tests for the {@see Base64Encoder} implementation ensuring correct
 * Base64 and URL-safe Base64 behaviour for encoding and decoding of binary-safe data.
 */
final class Base64EncoderTest extends TestCase {
    /**
     * Verifies that encoding and then decoding a payload results in the
     * original byte sequence (round-trip integrity).
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testEncodeDecodeRoundTrip(): void {
        $encoder = new Base64Encoder(new EncoderConfig(Characters::base64()));
        // Binary-safe payload including low/high ASCII bytes to ensure correctness
        $data = "\x00\x01Hello, World!\x7F";

        $encoded = $encoder->encode($data);
        self::assertIsString($encoded);

        $decoded = $encoder->decode($encoded);
        self::assertSame($data, $decoded);
    }

    /**
     * Ensures URL-safe encoding produces only URL-legal characters and that
     * the produced string decodes back to the original input.
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws RandomException
     */
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

    /**
     * Decoding an invalid Base64 string should raise an {@see InvalidArgumentException}.
     *
     * @return void
     */
    public function testDecodeThrowsOnInvalidBase64(): void {
        $this->expectException(InvalidArgumentException::class);
        (new Base64Encoder(new EncoderConfig(Characters::base64())))->decode('!!notbase64!!');
    }

    /**
     * URL-decoding an invalid Base64 string should raise an {@see InvalidArgumentException}.
     *
     * @return void
     */
    public function testUrlDecodeThrowsOnInvalidBase64(): void {
        $this->expectException(InvalidArgumentException::class);
        // invalid even after padding normalization
        (new Base64Encoder(new EncoderConfig(Characters::base64())))->urlDecode('**__invalid__**');
    }
}
