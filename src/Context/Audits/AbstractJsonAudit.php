<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Exceptions\AuditFailure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Base class for all JSON-centric audits.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
abstract class AbstractJsonAudit extends AbstractAudit
{
    /**
     * @var boolean If true, the audit will not trigger events.
     */
    public $silent = false;

    /**
     * Audits JSON structures specifically.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonStructure $input The input to be compared with the expected structure.
     * @param AbstractJsonStructure $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    abstract protected function auditJson(AbstractConstructure $constructure, AbstractJsonStructure $input, AbstractJsonStructure $expected): bool;

    /**
     * Ensures that the input and expected structures are both JSON structures before performing the audit.
     *
     * Also silences the audit if the option has been set.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param StructureInterface $input The input to be compared with the expected structure.
     * @param StructureInterface $expected The expected structure that the input should adhere to.
     * @return boolean
     * @throws AuditFailure
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        // Ensure that the input and expected arguments are JSON structures.

        if (is_subclass_of($input, AbstractJsonStructure::class) && is_subclass_of($expected, AbstractJsonStructure::class)) {

            // If the audit must be silent, silence event handlers and process.

            if ($this->silent()) {

                $constructure->getEventHandler()->mute();
    
                $result = $this->auditJson($constructure, $input, $expected);
    
                $constructure->getEventHandler()->unmute();
    
                return $result;
            }

            // Otherwise process the audit as normal.

            return $this->auditJson($constructure, $input, $expected);
        }

        // If either are not JSON structure, throw an error.

        throw new AuditFailure("Structure passed to JSON audits must be JSON structures.");
    }

    /**
     * Mutes any events handled by this instance of the audit.
     *
     * @return self
     */
    public function mute(): self
    {
        $this->silent = true;

        return $this;
    }

    /**
     * Unmutes any events handled by this instance of the audit.
     *
     * @return self
     */
    public function unmute(): self
    {
        $this->silent = false;

        return $this;
    }

    /**
     * Returns whether or not the audit is silent.
     *
     * @return boolean
     */
    public function silent(): bool
    {
        return $this->silent;
    }
}