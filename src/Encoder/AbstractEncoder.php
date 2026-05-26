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
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\id-forge
 * @category id-forge
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

namespace Inane\IdForge\Encoder;

use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Interface\EncoderInterface;
use Inane\Stdlib\Exception\InvalidArgumentException;
use Random\RandomException;

use function random_int;

/**
 * Base class for encoders that use a configurable alphabet
 *
 * Provides accessors for the configured alphabet and its length. Concrete
 * encoders (e.g., Base32/Base58/Base64) extend this class to share
 * configuration handling.
 */
abstract class AbstractEncoder implements EncoderInterface {
    /** @var EncoderConfig Encoder configuration (alphabet and derived values) */
    protected EncoderConfig $config;

    /**
     * @param EncoderConfig $config The configuration object for the encoder.
     *
     * @return void
     */
    public function __construct(EncoderConfig $config) {
        $this->config = $config;
    }

    /**
     * Returns the active alphabet for this encoder.
     *
     * @return string Alphabet characters in index order
     */
    protected function getAlphabet(): string {
        return $this->config->alphabet;
    }

    /**
     * Returns the length of the active alphabet.
     *
     * @return int Number of characters in the alphabet
     */
    protected function getAlphabetLength(): int {
        return $this->config->alphabetLength;
    }

    /**
     * Generates a cryptographically secure random string using this encoder's alphabet.
     *
     * Each character is chosen independently with uniform probability from the
     * configured alphabet using PHP's `random_int()`. This avoids modulo bias and
     * is suitable for tokens, nonces, and IDs where unpredictability matters.
     *
     * @param int $length Number of characters to generate (must be >= 0)
     *
     * @return string Random string of the requested length
     *
     * @throws InvalidArgumentException When $length is negative
     * @throws RandomException When the system CSPRNG fails
     */
    public function random(int $length): string {
        if ($length < 0) throw new InvalidArgumentException('Length must be greater than or equal to 0');

        if ($length === 0) return '';

        $alphabet = $this->getAlphabet();
        $alphaLen = $this->getAlphabetLength();

        // Guard against misconfiguration
        if ($alphaLen <= 0) throw new InvalidArgumentException('Alphabet must not be empty');

        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $alphaLen - 1);
            $out  .= $alphabet[$index];
        }

        return $out;
    }
}
