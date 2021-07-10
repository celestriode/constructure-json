<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;

/**
 * Checks the length of the input to be between a minimum and maximum. The input must be a primitive data types
 * (booleans, numbers, strings). If the input is not a string, it is converted to a string before checking the length.
 *
 * Extends the NumberRange audit for reduced code duplication.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class StringLength extends NumberRange
{
    const OUT_OF_RANGE = 'ee141934-785f-4e77-a299-5724c568ce9a';

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
        $length = strlen($input->getString());

        // Ensure the string length is within range.

        if ($this->withinRange($length)) {

            return true;
        }

        // String length out of range, audit failed.
        
        $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $this, $input, $expected);

        return false;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "string_length";
    }
}