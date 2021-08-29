<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\AuditInterface;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;

/**
 * Initiates comparison of a structure based on supplied conditions passing. This allows you to, for example, only check
 * for the existence of another field if a string contains a particular value. Audits supplied as predicates will not
 * trigger events when attempting to branch. Branches on their own are optional; use additional audits outside the branch
 * to ensure that the input is able to trigger a branch if branching is required based on the input.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class Branch extends AbstractJsonAudit
{
    const PASSED = '73c3602b-411f-4919-bf1f-e72389ea006f';

    /**
     * @var string The user-friendly name of the branch, used for logs or feedback.
     */
    protected $branchName;

    /**
     * @var AbstractJsonStructure The structure that will be compared to with the input if the predicates pass.
     */
    protected $branch;

    /**
     * @var AuditInterface[] The silent audits that must all pass before branching.
     */
    protected $predicates = [];

    /**
     * @param string $branchName The user-friendly name of the branch for feedback purposes.
     * @param AbstractJsonStructure $branch The structure to branch to if all predicates pass.
     * @param AuditInterface ...$predicates The audits that will silently run to determine whether to branch or not.
     */
    public function __construct(string $branchName, AbstractJsonStructure $branch, AuditInterface ...$predicates)
    {
        $this->branchName = $branchName;
        $this->branch = $branch;
        $this->predicates = $predicates;
    }

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
        // Silence the event handler when checking the predicates.

        $constructure->getEventHandler()->mute();

        // Cycle through all predicates.

        foreach ($this->getPredicates() as $predicate) {

            // If a predicate doesn't pass, then do not branch.

            if (!$predicate->audit($constructure, $input, $expected)) {

                // Unmute the event handler.

                $constructure->getEventHandler()->unmute();

                // Return true instead of false as branching is meant to be optional.

                return true;
            }
        }

        // All predicates passed, unmute event handler and begin branching.

        $constructure->getEventHandler()->unmute();

        $constructure->getEventHandler()->trigger(self::PASSED, $this, $input, $expected);

        // If the structures are objects, then add the branch's keys and the expected's keys to the input structure.

        $branch = $this->getBranch();

        if ($branch instanceof JsonObject && $input instanceof JsonObject && $expected instanceof JsonObject) {

            $input->addExpectedKeys(...($branch->getKeys()));
            $input->addExpectedKeys(...($expected->getKeys()));
        }

        // Return whether or not the branch comparison succeeds.

        return $branch->compare($constructure, $input);
    }

    /**
     * Returns the branch that this structure introduces if all predicates pass.
     *
     * @return AbstractJsonStructure
     */
    public function getBranch(): AbstractJsonStructure
    {
        return $this->branch;
    }

    /**
     * Returns all audits that must pass for the branch to pass.
     *
     * @return AuditInterface[]
     */
    public function getPredicates(): array
    {
        return $this->predicates;
    }

    /**
     * Returns the user-friendly name of the branch.
     *
     * @return string
     */
    public function getBranchName(): string
    {
        return $this->branchName;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "branch";
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $predicateStrings = array_map(function (AuditInterface $predicate) {

            return $predicate->toString();
        }, $this->getPredicates());

        return self::getName() . '{name=' . $this->getBranchName() . ',predicates=[' . implode(',', $predicateStrings) . ']}';
    }
}