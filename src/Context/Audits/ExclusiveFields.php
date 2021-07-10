<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;

/**
 * Takes in a list of keys where only one key in the list may exist within an object. If required() is invoked, then
 * one of those keys must exist. The audit would otherwise pass if no field with any of the accepted keys existed.
 *
 * For example, if the exclusive keys were "a" and "b", the input can only contain either "a" or "b" as a field key.
 * If both are included, an event is triggered.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class ExclusiveFields extends AbstractObjectAudit
{
    const KEY_CONFLICT = 'b28e4977-53a9-41f8-aea5-ac2635863b34';
    const NEEDS_ONE = '937a8a84-aab1-4db3-aeef-71ae07a1471c';

    /**
     * @var string[] The list of keys, of which only one is allowed to exist within an object.
     */
    protected $exclusiveKeys;

    /**
     * @var bool Whether or not one of the acceptable keys must exist.
     */
    protected $needsOne = false;

    /**
     * @param string ...$exclusiveKeys The list of keys, of which only one is allowed to exist within an object.
     */
    public function __construct(string ...$exclusiveKeys)
    {
        $this->exclusiveKeys = $exclusiveKeys;
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
        // Ensure that the input has only one of the specified exclusive keys.

        $matches = [];

        // Cycle through all exclusive keys and if it exists in the input, set it aside.

        foreach ($this->getExclusiveKeys() as $key) {

            // If the field exists, add it to the matches.

            if ($input->getChild($key) != null) {

                $matches[] = $key;
            }
        }

        // If there were 2 or more matches, return false.

        if (count($matches) >= 2) {

            $constructure->getEventHandler()->trigger(self::KEY_CONFLICT, $matches, $this, $input, $expected);

            return false;
        }

        // If there were 0 matches and 1 was required, return false.

        if (count($matches) == 0 && $this->isRequired()) {

            $constructure->getEventHandler()->trigger(self::NEEDS_ONE, $this, $input, $expected);

            return false;
        }

        // No conflict, return true.
        
        return true;
    }

    /**
     * Determines whether or not one of the accepted keys must exist within the object. An event will be fired if there
     * is no accepted key.
     *
     * @param bool $required Whether or not one of the accepted keys exists.
     * @return $this
     */
    public function required(bool $required = true): self
    {
        $this->needsOne = $required;

        return $this;
    }

    /**
     * Returns whether or not one of the accepted keys must exist within the object.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->needsOne;
    }

    /**
     * Returns all acceptable keys.
     *
     * @return string[]
     */
    public function getExclusiveKeys(): array
    {
        return $this->exclusiveKeys;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "exclusive_fields";
    }
}