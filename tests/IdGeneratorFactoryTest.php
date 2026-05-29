<?php

declare(strict_types=1);

namespace Inane\IdForge\Tests;

use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Config\SnowflakeConfig;
use Inane\IdForge\Generator\NanoidGenerator;
use Inane\IdForge\Generator\SnowflakeIdGenerator;
use Inane\IdForge\Generator\ULIDGenerator;
use Inane\IdForge\Generator\UUIDGenerator;
use Inane\IdForge\IdGeneratorFactory;
use PHPUnit\Framework\TestCase;

final class IdGeneratorFactoryTest extends TestCase {
    public function testCreateNanoid(): void {
        $gen = IdGeneratorFactory::createNanoid('abc', 10);
        self::assertInstanceOf(NanoidGenerator::class, $gen);
    }

    public function testCreateSnowflake(): void {
        $gen = IdGeneratorFactory::createSnowflake(1, 2, new SnowflakeConfig());
        self::assertInstanceOf(SnowflakeIdGenerator::class, $gen);
    }

    public function testCreateUUID(): void {
        $gen = IdGeneratorFactory::createUUID();
        self::assertInstanceOf(UUIDGenerator::class, $gen);
    }

    public function testCreateULID(): void {
        $gen = IdGeneratorFactory::createULID(new EncoderConfig(\Inane\IdForge\Config\Characters::base32()));
        self::assertInstanceOf(ULIDGenerator::class, $gen);
    }
}
