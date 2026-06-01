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
    EncoderConfig,
    SnowflakeConfig};
use Inane\IdForge\Generator\{
    NanoidGenerator,
    SnowflakeIdGenerator,
    ULIDGenerator,
    UUIDGenerator};
use Inane\IdForge\IdGeneratorFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class IdGeneratorFactoryTest
 *
 * Unit tests for the `IdGeneratorFactory` ensuring that each factory method
 * returns an instance of the expected concrete generator implementation.
 *
 * @package Inane\IdForge\Tests
 */
final class IdGeneratorFactoryTest extends TestCase {
    /**
     * Verifies that `createNanoid` returns a `NanoidGenerator` instance.
     */
    public function testCreateNanoid(): void {
        // Arrange & Act: create a nanoid generator with custom alphabet and size
        $gen = IdGeneratorFactory::createNanoid('abc', 10);

        // Assert: concrete type matches expectation
        self::assertInstanceOf(NanoidGenerator::class, $gen);
    }

    /**
     * Verifies that `createSnowflake` returns a `SnowflakeIdGenerator` instance
     * with default configuration values.
     */
    public function testCreateSnowflake(): void {
        // Arrange & Act: provide worker/datacentre identifiers and default config
        $gen = IdGeneratorFactory::createSnowflake(1, 2, new SnowflakeConfig());

        // Assert: concrete type matches expectation
        self::assertInstanceOf(SnowflakeIdGenerator::class, $gen);
    }

    /**
     * Verifies that `createUUID` returns a `UUIDGenerator` instance.
     */
    public function testCreateUUID(): void {
        // Act
        $gen = IdGeneratorFactory::createUUID();

        // Assert
        self::assertInstanceOf(UUIDGenerator::class, $gen);
    }

    /**
     * Verifies that `createULID` returns a `ULIDGenerator` instance when a
     * specific encoder configuration is supplied.
     */
    public function testCreateULID(): void {
        // Arrange: use base32 character set for the encoder
        $encoder = new EncoderConfig(Characters::base32());

        // Act
        $gen = IdGeneratorFactory::createULID($encoder);

        // Assert
        self::assertInstanceOf(ULIDGenerator::class, $gen);
    }
}
