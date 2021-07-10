<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Checks if the input structure and expected structure's values match. If the value of the expected structure is null,
 * then this will always return true. While in most cases the expected structure's value is null, this audit can be used
 * as a global audit for simpler structures where the input must be an exact value.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 *
 */
class ValuesMatch extends AbstractJsonAudit
{
    const VALUE_MISMATCH = '8f217f44-0de1-4bc1-be3a-90f2691540f9';

    /**
     * Audits JSON structures specifically.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonStructure $input The input to be compared with the expected structure.
     * @param AbstractJsonStructure $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    protected function auditJson(AbstractConstructure $constructure, AbstractJsonStructure $input, AbstractJsonStructure $expected): bool
    {
        // If the expected structure's value is null, return true by default.

        if ($expected->getValue() === null) {

            return true;
        }

        // Return true if the input and expected structures have the same value.

        if ($expected->equals($input)) {

            return true;
        }

        // Otherwise trigger an event and return false.

        $constructure->getEventHandler()->trigger(self::VALUE_MISMATCH, $this, $input, $expected);

        return false;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "inputs_match";
    }
}