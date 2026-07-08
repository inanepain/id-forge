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
 * Configuration for Snowflake-style ID generation.
 *
 * Controls the custom epoch and the bit allocation for worker, datacenter and
 * sequence components.
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

declare(strict_types=1);

namespace Inane\IdForge\Config;

/**
 * Configuration class for generating Snowflake IDs.
 * This class defines the settings for the Snowflake algorithm, including the custom epoch and bit allocations
 * for worker ID, datacenter ID and sequence.
 */
class SnowflakeConfig {
    /**
     * Represents an epoch in a temporal context.
     *
     * This property provides access to the current value representing
     * a specific point or interval on the timescale used by this object.
     *
     * return int Custom epoch in milliseconds
     */
    public int $epoch {
        get => $this->epoch;
    }

    /**
     * Gets the number of bits allocated to uniquely identify a worker within this context.
     *
     * @return int The bit length used for identifying workers
     */
    public int $workerIdBits {
        get => $this->workerIdBits;
    }

    /**
     * Gets the value of datacenter ID bits.
     *
     * This property returns an integer representing specific settings related to a data center's identifier. These settings are crucial for ensuring that resources within different
     * environments can be distinguished and managed correctly by software systems, particularly in cloud computing platforms or distributed architectures where multiple
     * instances may exist across various physical locations.
     *
     * @return int The value of the datacenter ID bits as an integer representing specific configurations related to a data center's identifier.
     */
    public int $datacenterIdBits {
        get => $this->datacenterIdBits;
    }

    /**
     * Gets the sequence number for this entity or resource within its respective group/space allocated by other properties like datacenterId and instanceID.
     *
     * The 'sequence bits' property is often used in distributed systems to provide a unique identifier that combines with other identifiers such as
     * Datacenter ID, Instance ID, etc. This helps ensure the uniqueness of an object across different clusters or regions when deployed on cloud platforms,
     * multi-tenant environments or any scenario where resource allocation and identification are crucial for system scalability.
     *
     * The sequence number is typically a part of larger identifier components that collectively create unique identifiers ensuring minimal collision probabilities
     * in distributed systems. It's essential to increment this value appropriately whenever new instances/objects/resources need to be created within the same group,
     * space or environment, maintaining orderly and consistent sequencing which aids in resource management tasks like allocation tracking.
     *
     * @return int The sequence number as an integer representing a unique identifier for entities/reresources when combined with other properties
     */
    public int $sequenceBits {
        get {
            return $this->sequenceBits;
        }
    }

    /**
     * Constructor for initializing the class properties.
     *
     * @param int $epoch            Custom epoch in milliseconds.
     * @param int $workerIdBits     Number of bits allocated for the worker ID.
     * @param int $datacenterIdBits Number of bits allocated for the datacenter ID.
     * @param int $sequenceBits     Number of bits allocated for the sequence.
     *
     * @return void
     */
    public function __construct(int $epoch = 1609459200000, int $workerIdBits = 5, int $datacenterIdBits = 5, int $sequenceBits = 12) {
        $this->epoch = $epoch;
        $this->workerIdBits = $workerIdBits;
        $this->datacenterIdBits = $datacenterIdBits;
        $this->sequenceBits = $sequenceBits;
    }
}
