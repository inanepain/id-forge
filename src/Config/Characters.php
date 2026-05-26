<?php

/**
 * Inane: IdForge
 * Inane Encoder & ID Library
 * $Id$
 * $Date$
 * PHP version 8.5
 * Provides configuration values for encoders, primarily the alphabet and its
 * derived length.
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\id-forge
 * @category id-forge
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\IdForge\Config;

use Inane\Stdlib\Bitmask\EnumBitmaskTrait;

use function str_replace;

/**
 * Characters
 *
 * Character sets and bitmask flags for ID generation alphabets.
 *
 * @version 1.0.0
 * @package Inane\IdForge\Config
 */
enum Characters: int {
    use EnumBitmaskTrait;

    /**
     * Uppercase characters (A-Z)
     */
    case useUPPER = 1 << 0;

    /**
     * Lowercase characters (a-z)
     */
    case useLower = 1 << 1;

    /**
     * Numerical characters (0-9)
     */
    case useNumeric = 1 << 2;

    /**
     * Special characters/symbols (+/)
     */
    case useSymbol = 1 << 3;

    /**
     * Alphabet UPPER: ABCDEFGHIJKLMNOPQRSTUVWXYZ
     */
    const string alphaUPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Alphabet lower: abcdefghijklmnopqrstuvwxyz
     */
    const string alphaLower = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * Alphabet numerical: 0123456789
     */
    const string numerical  = '0123456789';

    /**
     * Alphabet special: +/
     */
    const string special    = '+/';

    /**
     * Generate Base64 alphabet
     *
     * Constructs a string containing characters for a Base64 alphabet
     * based on the provided bitmask.
     *
     * @param int $mask Character type bitmask
     *
     * @return string Generated alphabet
     */
    public static function base64(int $mask = self::useUPPER->value | self::useLower->value | self::useNumeric->value | self::useSymbol->value): string {
        $string = '';

        // Add Uppercase
        if (self::has($mask, self::useUPPER)) $string .= self::alphaUPPER;

        // Add Lowercase
        if (self::has($mask, self::useLower)) $string .= self::alphaLower;

        // Add Numbers
        if (self::has($mask, self::useNumeric)) $string .= self::numerical;

        // Add Symbols
        if (self::has($mask, self::useSymbol)) $string .= self::special;

        return $string;
    }

    /**
     * Generate Base58 alphabet
     *
     * Constructs a string containing characters for a Base58 alphabet.
     * Removes visually similar characters: 0, I, O, l.
     *
     * @param int $mask Character type bitmask
     *
     * @return string Generated alphabet
     */
    public static function base58(int $mask = self::useUPPER->value | self::useLower->value | self::useNumeric->value): string {
        $string = '';

        // Add Uppercase
        if (self::has($mask, self::useUPPER)) $string .= self::alphaUPPER;

        // Add Lowercase
        if (self::has($mask, self::useLower)) $string .= self::alphaLower;

        // Add Numbers
        if (self::has($mask, self::useNumeric)) $string .= self::numerical;

        // Remove confusing characters
        return str_replace([
            '0',
            'I',
            'O',
            'l',
        ], '', $string);
    }

    /**
     * Generate Base32 alphabet
     *
     * Constructs a string containing characters for a Base32 alphabet.
     * Removes 0, 1, 8, 9 to reach 32 characters when used with UPPER or lower + number.
     *
     * @param int $mask Character type bitmask
     *
     * @return string Generated alphabet
     */
    public static function base32(int $mask = self::useUPPER->value | self::useLower->value | self::useNumeric->value): string {
        $string = '';

        // Add Uppercase
        if (self::has($mask, self::useUPPER)) $string .= self::alphaUPPER;

        // Add Lowercase
        if (self::has($mask, self::useLower)) $string .= self::alphaLower;

        // Add Numbers
        if (self::has($mask, self::useNumeric)) $string .= self::numerical;

        // Remove characters to fit 32 or avoid confusion
        return str_replace([
            '0',
            '1',
            '8',
            '9',
        ], '', $string);
    }
}
