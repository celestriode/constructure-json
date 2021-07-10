<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\JsonBoolean;

/**
 * Audits that specifically deal with JsonBoolean objects.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractBooleanAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = '862961af-9d48-4d75-9054-aa9384b0c833';

    /**
     * Audits two primitive structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param JsonBoolean $input The input to be compared with the expected structure.
     * @param JsonBoolean $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditBoolean(AbstractConstructure $constructure, JsonBoolean $input, JsonBoolean $expected): bool;

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
        // Return false if either of the structure aren't JsonBooleans.

        if (!($input instanceof JsonBoolean) || !($expected instanceof JsonBoolean)) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        // Otherwise, run the audit with JsonBooleans.

        return $this->auditBoolean($constructure, $input, $expected);
    }
}