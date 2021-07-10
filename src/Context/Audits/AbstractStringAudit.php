<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\JsonString;

/**
 * Audits that specifically deal with JsonString objects.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractStringAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = 'ccec695f-2b3c-4fb0-b9f0-f55b1e848498';

    /**
     * Audits two string structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param JsonString $input The input to be compared with the expected structure.
     * @param JsonString $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditString(AbstractConstructure $constructure, JsonString $input, JsonString $expected): bool;

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
        // Return false if either of the structure aren't JsonStrings.

        if (!($input instanceof JsonString) || !($expected instanceof JsonString)) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        // Otherwise, run the audit with JsonStrings.

        return $this->auditString($constructure, $input, $expected);
    }
}