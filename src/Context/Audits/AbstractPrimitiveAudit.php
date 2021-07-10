<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;

/**
 * Audits that specifically deal with AbstractJsonPrimitive objects.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractPrimitiveAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = '9f672370-7cc1-4d72-9938-16b60ef22b29';

    /**
     * Audits two primitive structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonPrimitive $input The input to be compared with the expected structure.
     * @param AbstractJsonPrimitive $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditPrimitive(AbstractConstructure $constructure, AbstractJsonPrimitive $input, AbstractJsonPrimitive $expected): bool;

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
        // Ensure both are JSON primitives.

        if (!($input instanceof AbstractJsonPrimitive) || !($expected instanceof AbstractJsonPrimitive)) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        return $this->auditPrimitive($constructure, $input, $expected);
    }

}