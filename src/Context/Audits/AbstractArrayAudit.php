<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\JsonArray;

/**
 * Audits that specifically deal with JsonArray objects.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractArrayAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = '49e37781-a48c-42d0-b04c-c15a1d7c7cbb';

    /**
     * Audits two array structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param JsonArray $input The input to be compared with the expected structure.
     * @param JsonArray $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditArray(AbstractConstructure $constructure, JsonArray $input, JsonArray $expected): bool;

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
        // Return false if either of the structure aren't JsonArrays.

        if (!($input instanceof JsonArray) || !($expected instanceof JsonArray)) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        // Otherwise, run the audit with JsonArrays.

        return $this->auditArray($constructure, $input, $expected);
    }
}