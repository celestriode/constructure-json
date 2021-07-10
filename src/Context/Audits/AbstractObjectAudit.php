<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;

/**
 * Audits that specifically deal with JsonObject objects.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractObjectAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = '41d7e59a-1fd9-43d5-b4e9-af62bb612c8e';

    /**
     * Audits two objects.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param JsonObject $input The input to be compared with the expected structure.
     * @param JsonObject $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditObject(AbstractConstructure $constructure, JsonObject $input, JsonObject $expected): bool;

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
        // Return false if either of the structure aren't JsonObjects.

        if (!($input instanceof JsonObject) || !($expected instanceof JsonObject)) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        // Otherwise, run the audit with JsonObjects.

        return $this->auditObject($constructure, $input, $expected);
    }
}