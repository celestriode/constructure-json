<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonParent;

/**
 * Audits that specifically deal with AbstractJsonParent objects.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractParentAudit extends AbstractJsonAudit
{
    const INVALID_STRUCTURE = '44d0bb32-e3f1-4e27-a4f0-6c4f8badb782';

    /**
     * Audits two parent structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonParent $input The input to be compared with the expected structure.
     * @param AbstractJsonParent $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditParent(AbstractConstructure $constructure, AbstractJsonParent $input, AbstractJsonParent $expected): bool;

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
        // Return false if either of the structure aren't AbstractJsonParents.

        if (!($input instanceof AbstractJsonParent) || !($expected instanceof AbstractJsonParent)) {

            $constructure->getEventHandler()->trigger(self::INVALID_STRUCTURE, $this, $input, $expected);

            return false;
        }

        // Otherwise, run the audit with AbstractJsonParents.

        return $this->auditParent($constructure, $input, $expected);
    }
}