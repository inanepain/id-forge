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
use Inane\Stdlib\Exception\InvalidArgumentException;
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
     * Verifies that `createSnowflake` returns a `SnowflakeIdGenerator` instance when specific worker and data centre identifiers are provided along with the default configuration.
     *
     * This test ensures that given valid parameters for workers and data centers,
     * as well as using the SnowflakeConfig by default, result in an instantiated
     * object of type SnowflakeIdGenerator which adheres to expected behavior according
     * to defined specifications or constraints. It's a part of validation ensuring
     * correct implementation within context-specific boundaries.
     *
     * @throws InvalidArgumentException
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
