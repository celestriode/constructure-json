<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;

/**
 * Checks whether a child in the object has a specific value from a list of values. The structure that this audit is
 * attached to must be a JsonObject. The child must be a primitive data type (booleans, numbers, strings).
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class ChildHasValue extends AbstractObjectAudit
{
    const INVALID_CHILD = '0ebc673a-bca8-4d11-a419-8e207bddd8b7';
    const INVALID_VALUE = 'a8e4f4a3-f361-4f2a-ac9a-9ed6bfadab23';

    /**
     * @var string The key of the child to find within the object.
     */
    protected $key;

    /**
     * @var array The values that the child is allowed to have.
     */
    protected $values = [];

    /**
     * @param string $key The key of the child to check the values of.
     * @param mixed ...$values The values that the child is allowed to have.
     */
    public function __construct(string $key, ...$values)
    {
        $this->key = $key;
        $this->values = $values;
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
        // Ensure the child exists.

        $child = $input->getChild($this->getKey());

        if ($child === null || !($child instanceof AbstractJsonPrimitive)) {

            $constructure->getEventHandler()->trigger(self::INVALID_CHILD, $this, $input, $expected);

            return false;
        }

        // If the input has the accepted values, return true.

        if (in_array($child->getValue(), $this->getValues())) {

            return true;
        }

        // Otherwise, return false.

        $constructure->getEventHandler()->trigger(self::INVALID_VALUE, $this, $input, $expected);

        return false;
    }

    /**
     * Returns the key of the child that must exist.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the values that the child may have.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "child_has_value";
    }
}