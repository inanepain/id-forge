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
 * @author Philip Michael Raab<peep@inane.co.za>
 * @package inanepain\id-forge
 * @category id-forge
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\IdForge;

use Inane\Stdlib\Exception\InvalidArgumentException;

use function assert;
use function bin2hex;
use function chr;
use function dechex;
use function exec;
use function explode;
use function hex2bin;
use function hexdec;
use function md5;
use function microtime;
use function ord;
use function preg_match;
use function random_bytes;
use function random_int;
use function sha1;
use function sprintf;
use function str_pad;
use function str_replace;
use function str_split;
use function stripos;
use function strlen;
use function substr;
use function substr_replace;
use function vsprintf;

use const PHP_OS_FAMILY;
use const STR_PAD_LEFT;

/**
 * Class UUIDTool
 *
 * Provides utility functions for generating and manipulating Universally Unique Identifiers.
 *
 * supported:
 * - v1 (time + machine based) Time-based. Uses timestamp + MAC address (or random node ID). Not fully private, can leak creation time and hardware ID.
 * - v3 (deterministic hash-based) Name-based, MD5 hash. Deterministic: same input namespace + name: same UUID.
 * - v4 (random) Random-based. Fully random (122 random bits). Most common for general use.
 * - v5 (deterministic hash-based) Name-based SHA-1 hash. Like v3 but stronger hash.
 * - v7 (improved time-ordered) Unix epoch time + randomness. Monotonic, human-friendly timestamps, efficient for ordering.
 *
 * UUIDs (Universally Unique Identifiers) have different versions defined in RFC 4122. Each version specifies how the 128-bit UUID is generated.
 *  - Version 1: Time-based. Uses timestamp + MAC address (or random node ID). Not fully private, can leak creation time and hardware ID.
 *  - Version 2: DCE Security. Similar to v1 but embeds POSIX UID/GID. Rarely used.
 *  - Version 3: Name-based, MD5 hash. Deterministic: same input namespace + name: same UUID.
 *  - Version 4: Random-based. Fully random (122 random bits). Most common for general use.
 *  - Version 5: Name-based SHA-1 hash. Like v3 but stronger hash.
 *  - Version 6: Time-ordered (proposed, newer). Like v1 but rearranged bits for better database indexing.
 *  - Version 7: Unix epoch time + randomness. Monotonic, human-friendly timestamps, efficient for ordering.
 *  - Version 8: Custom. Reserved for application-specific formats.
 * Key point:
 *  - v1/v2: time + machine based.
 *  - v3/v5: deterministic hash-based.
 *  - v4: random.
 *  - v6/v7: improved time-ordered.
 *  - v8: free-form.
 *
 * @version 0.1.0
 */
class UUIDTool {
    //#region Class Constants
    /**
     * When this namespace is specified, the name string is a fully qualified
     * domain name
     *
     * @link http://tools.ietf.org/html/rfc4122#appendix-C RFC 4122, Appendix C: Some Name Space IDs
     */
    public const string NAMESPACE_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    /**
     * When this namespace is specified, the name string is a URL
     *
     * @link http://tools.ietf.org/html/rfc4122#appendix-C RFC 4122, Appendix C: Some Name Space IDs
     */
    public const string NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    /**
     * When this namespace is specified, the name string is an ISO OID
     *
     * @link http://tools.ietf.org/html/rfc4122#appendix-C RFC 4122, Appendix C: Some Name Space IDs
     */
    public const string NAMESPACE_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    /**
     * When this namespace is specified, the name string is an X.500 DN in DER
     * or a text output format
     *
     * @link http://tools.ietf.org/html/rfc4122#appendix-C RFC 4122, Appendix C: Some Name Space IDs
     */
    public const string NAMESPACE_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
    //#endregion Class Constants

    //#region Properties
    /**
     * Stores the MAC address as a string.
     *
     * @var string
     */
    private static string $mac;
    //#endregion Properties

    #region Utilities

    /**
     * Retrieves the MAC address of the current machine.
     *
     * @return string|null Returns the MAC address as a string if found, or null if not available.
     */
    public static function getMacAddress(): ?string {
        if (isset(static::$mac)) return static::$mac;

        $mac = null;
        if (stripos(PHP_OS_FAMILY, 'WIN') === 0) {
            // Windows
            exec('getmac /NH', $output);

            foreach($output as $line) {
                if (empty($line)) continue;
                [
                    $address,
                    $status,
                ] = explode('   ', $line);
                if ($status !== 'Media disconnected') {
                    $mac = str_replace('-', ':', $address);
                    break;
                }
            }
        } else {
            // Unix/Linux/Mac
            exec('ifconfig -a', $output);
            // loop over output looking for mac address
            foreach($output as $line) {
                if ($mac) { // if found, check next few detail lines to verify active state
                    if (str_starts_with($line, "\t")) {
                        if (str_contains($line, ' active')) break;
                    } else { // once indent ends ots a new interface
                        $mac = null;
                    }
                } else {
                    if (preg_match('/([a-f0-9]{2}:){5}[a-f0-9]{2}/i', $line, $matches)) {
                        $mac = $matches[0];
                    }
                }
            }
        }

        if ($mac && !isset(static::$mac)) static::$mac = $mac;

        return static::$mac;
    }

    /**
     * Verifies if the given namespace string is in a valid UUID format.
     *
     * @param string $namespace The namespace string to verify.
     *
     * @return bool Returns true if the namespace is a valid UUID format and version if specified, false otherwise.
     */
    public static function uuidVerifyFormat(string $namespace): bool {
        return (bool)preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $namespace);
    }

    /**
     * Verifies if the given namespace string is in a valid UUID format.
     *
     * @param string $namespace The namespace string to verify.
     * @param int    $version   Optional UUID version to verify against.
     *
     * @return bool Returns true if the namespace is a valid UUID format and version is correct.
     *
     * @throws InvalidArgumentException
     */
    public static function uuidVerifyVersion(string $namespace, int $version = 3): bool {
        return static::getVersion($namespace) === $version;
    }

    /**
     * Extracts and returns the version number of the given UUID.
     *
     * The method verifies the format of the provided UUID and, if valid,
     * extracts the version number encoded in the UUID.
     *
     * @param string $uuid The UUID to extract the version from. Must be in a valid UUID format.
     *
     * @return int The version number of the provided UUID.
     *
     * @throws InvalidArgumentException If the provided UUID does not have a valid format.
     */
    public static function getVersion(string $uuid): int {
        if (!static::uuidVerifyFormat($uuid)) {
            throw new InvalidArgumentException('Invalid UUID format.');
        }

        return (int)$uuid[14];
    }
    #endregion Utilities

    #region UUID Generators
    /**
     * Generates a version 1 (time-based) UUID.
     *
     * If a MAC address is provided, it will be used in the UUID generation.
     * Otherwise, it will try being read from a system.
     * Finally, a random MAC address will be used.
     *
     * @param string|null $mac Optional MAC address to use for UUID generation, null to use machine MAC address.
     *
     * @return string The generated UUID v1.
     *
     * @throws \Random\RandomException
     */
    public static function v1(?string $mac = null) {
        // Get the time in 100-nanosecond intervals since UUID epoch (1582-10-15)
        $time = microtime(true) * 10_000_000 + 0x01B21DD213814000;

        // Split into time_low, time_mid, and time_hi_and_version
        $timeLow = $time & 0xFFFFFFFF;
        $timeMid = ($time >> 32) & 0xFFFF;
        $timeHi = ($time >> 48) & 0x0FFF;
        $timeHiAndVersion = $timeHi | (1 << 12); // Version 1

        // Generate clock sequence
        $clockSeq = random_int(0, 0x3FFF);
        $clockSeqHi = ($clockSeq >> 8) | 0x80; // Variant RFC4122
        $clockSeqLow = $clockSeq & 0xFF;

        // Get node (use a random 48-bit number or MAC address if available)
        if ($mac) {
            $node = str_replace(':', '', $mac);
        } elseif ($mac = static::getMacAddress()) {
            $node = str_replace(':', '', $mac);
        } else {
            $node = bin2hex(random_bytes(6));
        }
        $node = substr_replace($node, dechex(hexdec(substr($node, 0, 2)) | 0x01), 0, 2); // Set multicast bit

        return sprintf('%08x-%04x-%04x-%02x%02x-%012s', $timeLow, $timeMid, $timeHiAndVersion, $clockSeqHi, $clockSeqLow, $node);
    }

    /**
     * Generates a version 3 (name-based) UUID.
     *
     * Combines a namespace UUID and a name and generates a UUID based on the MD5 hash of their binary representations.
     *
     * @param string $namespace The namespace UUID, which must be in a valid UUID format.
     * @param string $name      The name used to generate the UUID within the namespace.
     *
     * @return string The generated UUID v3 as a string.
     *
     * @throws InvalidArgumentException If the provided namespace UUID has an invalid format.
     */
    public static function v3(string $namespace, string $name): string {
        if (!static::uuidVerifyFormat($namespace)) throw new InvalidArgumentException('Invalid namespace UUID format.');

        $n_hex = str_replace([
            '-',
            '{',
            '}',
        ], '', $namespace);                                    // Getting hexadecimal components of namespace
        $binary_str = '';                                      // Binary Value

        //Namespace UUID to bits conversion
        for($i = 0, $iMax = strlen($n_hex); $i < $iMax; $i += 2) {
            $binary_str .= chr(hexdec($n_hex[$i] . $n_hex[$i + 1]));
        }

        //hash value
        $hashing = md5($binary_str . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',               // 32 bits for the time low
            substr($hashing, 0, 8),                              // 16 bits for the time mid
            substr($hashing, 8, 4),                              // 16 bits for the time hi,
            (hexdec(substr($hashing, 12, 4)) & 0x0fff) | 0x3000, // 8 bits and 16 bits for the clk_seq_hi_res,
            // 8 bits for the clk_seq_low,
            (hexdec(substr($hashing, 16, 4)) & 0x3fff) | 0x8000, // 48 bits for the node
            substr($hashing, 20, 12),);
    }

    /**
     * Generates a version 4 (random) UUID.
     *
     * If a string is provided as $data, it will be used as the source of randomness.
     * Otherwise, a random UUID will be generated.
     *
     * @param string|null $data Optional data to use for UUID generation.
     *
     * @return string The generated UUID v4 as a string.
     *
     * @throws \Random\RandomException
     */
    public static function v4(?string $data = null): string {
        if ($data) {
            switch (strlen($data)) {
                case 36:
                    $data = str_replace('-', '', $data);
                case 32:
                    $data = hex2bin($data);
            }
        } else $data = random_bytes(16); // Generate 16 bytes (128 bits) of random data or use the data passed into the function.

        // make sure string length is 16 characters
        assert(strlen($data) === 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36-character UUID.
        return $data
                |> bin2hex(...)
                |> (fn($x) => str_split($x, 4))
                |> (fn($x) => vsprintf('%s%s-%s-%s-%s-%s%s%s', $x));
    }

    /**
     * Generates a version 5 (namespace-based) UUID.
     *
     * This method creates a UUID v5 using a namespace UUID and a name to generate a SHA-1 based UUID.
     * The namespace UUID must be provided in a valid UUID format, and the
     * name is combined with the namespace for hashing to produce the UUID.
     *
     * @param string $namespace The namespace UUID in a valid format.
     * @param string $name      The name to be combined with the namespace to generate the UUID.
     *
     * @return string The generated UUID v5 as a string.
     *
     * @throws InvalidArgumentException If the $namespace is not in a valid UUID format.
     */
    public static function v5(string $namespace, string $name): string {
        if (!static::uuidVerifyFormat($namespace)) throw new InvalidArgumentException('Invalid namespace UUID format.');

        $namespace = str_replace('-', '', $namespace);
        $hash = sha1(hex2bin($namespace) . $name);

        // Format UUID v5
        return sprintf('%08s-%04s-%04x-%04x-%12s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000, // version 5
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, // variant
            substr($hash, 20, 12),
        );
    }

    /**
     * Generates a UUID version 7 string.
     *
     * UUIDv7 is time-ordered and includes a timestamp component.
     *
     * @param int|null $milliseconds Optional Unix timestamp in milliseconds. If null, current time is used.
     *
     * @return string The generated UUIDv7 string.
     *
     * @throws \Random\RandomException
     */
    public static function v7(?int $milliseconds = null): string {
        static $last_timestamp = 0;

        if ($milliseconds && strlen((string)$milliseconds) > 13) {
            $milliseconds = (int)substr((string)$milliseconds, 0, 13);
        } elseif ($milliseconds && strlen((string)$milliseconds) < 13) {
            $milliseconds = (int)str_pad((string)$milliseconds, 13, '0', \STR_PAD_RIGHT);
        }

        $epoch_ms = $milliseconds ?: (int)(microtime(true) * 100000);
        if ($last_timestamp >= $epoch_ms) $epoch_ms = $last_timestamp + 1;

        $last_timestamp = $epoch_ms;
        $data = random_bytes(10);
        $data[0] = chr((ord($data[0]) & 0x0f) | 0x70); // set version
        $data[2] = chr((ord($data[2]) & 0x3f) | 0x80); // set variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($epoch_ms
                |> dechex(...)
                |> (fn($x) => substr($x, 0, 12))
                |> (fn($x) => str_pad($x, 12, '0', STR_PAD_LEFT) . bin2hex($data)), 4));
    }
    #endregion UUID Generators
}
