<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;
use Celestriode\JsonConstructure\Structures\Types\JsonDouble;
use Celestriode\JsonConstructure\Structures\Types\JsonInteger;

/**
 * Audits that specifically deal with AbstractJsonPrimitive objects that are either JsonInteger or JsonDouble audits.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractNumericAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = 'ecf5022b-ce27-44d5-87c2-c2a176bf5683';

    /**
     * Audits two primitive structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonPrimitive $input The input to be compared with the expected structure.
     * @param AbstractJsonPrimitive $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditNumber(AbstractConstructure $constructure, AbstractJsonPrimitive $input, AbstractJsonPrimitive $expected): bool;

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
        // Return false if either of the structure aren't JsonIntegers or JsonDoubles.

        if ((!($input instanceof JsonInteger) || !($expected instanceof JsonInteger)) && (!($input instanceof JsonDouble) || !($expected instanceof JsonDouble))) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        // Otherwise, run the audit with JsonPrimitives.

        return $this->auditNumber($constructure, $input, $expected);
    }
}