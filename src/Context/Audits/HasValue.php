<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;

/**
 * Checks whether or not the primitive structure has a value from a list of values. Attach this audit directly to any
 * primitive data type (booleans, numbers, strings).
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class HasValue extends AbstractPrimitiveAudit
{
    const INVALID_VALUE = '53fb094f-62ca-4853-9d57-9b100e22eb52';

    /**
     * @var array The values that the structure is allowed to have.
     */
    protected $values = [];

    /**
     * @param mixed ...$values The values that the structure is allowed to have.
     */
    public function __construct(...$values)
    {
        $this->values = $values;
    }

    /**
     * Audits two primitive structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonPrimitive $input The input to be compared with the expected structure.
     * @param AbstractJsonPrimitive $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    protected function auditPrimitive(AbstractConstructure $constructure, AbstractJsonPrimitive $input, AbstractJsonPrimitive $expected): bool
    {
        $value = $input->getValue();

        // If the value existed in the list of accepted values, return true.
        
        if ($this->valueMatches($value, $this->getValues())) {

            return true;
        }

        // Otherwise, trigger event and return false.

        $constructure->getEventHandler()->trigger(self::INVALID_VALUE, $this, $input, $expected);

        return false;
    }

    /**
     * Returns whether or not the input value is within the array of values.
     *
     * @param mixed $value The value to validate.
     * @param array $values The list of values that the input should belong to.
     * @return bool
     */
    protected function valueMatches($value, array $values): bool
    {
        return in_array($value, $values);
    }

    /**
     * Returns all values accepted by this audit.
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
        return "has_value";
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return self::getName() . '{values=[' . implode(',', $this->getValues()) . ']}';
    }
}