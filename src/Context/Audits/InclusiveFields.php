<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;

/**
 * Checks whether or not an object has all keys listed.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class InclusiveFields extends AbstractObjectAudit
{
    const MISSING_KEYS = '2d1b4a9b-ec54-4846-9e08-a1e4540f23aa';

    /**
     * @var string[] The keys that the object must have.
     */
    protected $keys;

    /**
     * @param string ...$keys The keys that the object must have.
     */
    public function __construct(string ...$keys)
    {
        $this->keys = $keys;
    }

    /**
     * Audits two objects.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param JsonObject $input The input to be compared with the expected structure.
     * @param JsonObject $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    protected function auditObject(AbstractConstructure $constructure, JsonObject $input, JsonObject $expected): bool
    {
        // Ensure the keys that are expected exist in the input.

        $success = true;
        $missingKeys = [];

        // Cycle through each of the required keys.

        foreach ($this->getKeys() as $key) {

            // If the input does not have the expected key, the audit fails. Continue cycling to get the rest.

            if ($input->getChild($key) === null) {

                $success = false;
                $missingKeys[] = $key;
            }
        }

        // If the audit failed, trigger an event.

        if (!$success) {

            $constructure->getEventHandler()->trigger(self::MISSING_KEYS, $missingKeys, $this, $input, $expected);
        }

        // Return whether or not all required keys existed.

        return $success;
    }

    /**
     * Returns the keys that the object must have.
     *
     * @return string[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "inclusive_fields";
    }
}