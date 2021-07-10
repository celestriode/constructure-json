<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Compares the JSON type of the input to the expected structure. If the input is null and the expected structure is
 * nullable, this audit passes under the philosophy that a null data type is also any data type.
 *
 * This is the primary audit to use for structural verification. Use it as a global audit or replace it with something
 * similar that better fits your needs.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class TypesMatch extends AbstractJsonAudit
{
    const TYPES_MISMATCHED = '1df14cb4-9b65-46fb-93a7-311928bbf8e2';

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
        // If the input is null and is allowed to be null, then technically the type is nothing.

        if ($input->getValue() === null) {

            if ($expected->isNullable()) {

                return true;
            }

            $constructure->getEventHandler()->trigger(self::TYPES_MISMATCHED, $this, $input, $expected);

            return false;
        }

        // Otherwise check if the types match.

        if (!$expected->typesMatch($input)) {

            $constructure->getEventHandler()->trigger(self::TYPES_MISMATCHED, $this, $input, $expected);

            return false;
        }

        // If types are a match, return true.

        return true;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "types_match";
    }
}