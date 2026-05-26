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

use function strlen;

/**
 * Configuration for encoders
 * Stores the alphabet and precomputes its length for faster access in
 * performance-sensitive code paths.
 */
class EncoderConfig {
    /** @var string Characters that make up the encoding alphabet */
    public string $alphabet {
        get => $this->alphabet;
    }

    /** @var int Cached length of the alphabet */
    public int $alphabetLength {
        get => $this->alphabetLength;
    }

    /**
     * @param string $alphabet A string representing the alphabet to be used.
     *
     * @return void
     */
    public function __construct(string $alphabet) {
        $this->alphabet = $alphabet;
        $this->alphabetLength = strlen($alphabet);
    }
}
