<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Context\Audits\TypesMatch;
use Celestriode\JsonConstructure\Exceptions\AuditFailure;
use Celestriode\JsonConstructure\Exceptions\JsonTypeError;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Represents a JSON array. It acts as a parent, where its children are the elements.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonArray extends AbstractJsonParent
{
    const INVALID_INPUT = '91e44512-db46-4201-ae2c-43cba6c7cd39';

    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return "array";
    }

    /**
     * Sets the raw input of the structure that would result in this object being created.
     *
     * @param mixed $input The input, whatever it may be.
     * @return self
     * @throws JsonTypeError
     */
    public function setValue($input = null): StructureInterface
    {
        // If the input is not an array, throw an error.

        if ($input !== null && !is_array($input)) {

            throw new JsonTypeError("Input must be an array.");
        }

        // Otherwise add it as normal.

        return parent::setValue($input);
    }

    /**
     * Adds multiple children. The names of the elements will be the name of the
     * array itself, if present.
     *
     * @param AbstractJsonStructure ...$elements The elements to add.
     * @return self
     */
    public function addElements(AbstractJsonStructure ...$elements): self
    {
        foreach ($elements as $element) {

            $this->addElement($element);
        }

        return $this;
    }

    /**
     * Adds a child. The name of the element will be the name of the array itself,
     * if present.
     *
     * @param AbstractJsonStructure $element The element to add.
     * @return AbstractJsonStructure
     */
    public function addElement(AbstractJsonStructure $element): AbstractJsonStructure
    {
        return $this->addChild(null, $element);
    }

    /**
     * Takes in another structure and compares its structure to this one.
     *
     * With an expected array structure, it acts similarly to a mixed structure:
     * for every input element, it is compared to the expected array's children
     * to find a match.
     *
     * @param AbstractConstructure $constructure
     * @param StructureInterface $other
     * @return boolean
     * @throws AuditFailure
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        // Do parental comparison first.

        $success = parent::compare($constructure, $other);

        // If the input is not an array, then elements cannot be compared.

        if (!($other instanceof JsonArray)) {

            $constructure->getEventHandler()->trigger(self::INVALID_INPUT, $constructure, $other, $this);

            return false;
        }

        // Cycle through all elements on the input...

        $failedCount = 0;

        // Create a temporary audit for type matching.

        $typesAudit = TypesMatch::get();

        foreach ($other->getChildren() as $element) {

            $matched = false;

            // And for each element in the input, check if it is acceptable.

            /**
             * @var AbstractJsonStructure $expectedElement
             * @var AbstractJsonStructure $element
             */
            foreach ($this->getChildren() as $expectedElement) {

                // Mute the event handler for a silent audit.

                //$constructure->getEventHandler()->mute(); // TODO: silence?

                // Use the audit to check the data types.

                $constructure->getEventHandler()->unmute();

                if ($typesAudit->audit($constructure, $element, $expectedElement)) {

                    // If the elements are comparable, mark the element as having found a match.

                    if ($expectedElement->compare($constructure, $element)) {

                        $matched = true;
                    }

                    // If they were comparable but the comparison failed, increment the failure count.
                    
                    else {

                        // TODO: event.
                        var_dump("ARRAY: comparable elements failed to compare. " . $element->getValue());

                        $failedCount++;
                    }
                }
            }

            // If this element did not have a match, then that counts as a failure.

            if (!$matched) {

                // TODO: event.
                //$constructure->getEventHandler()->trigger(self::)

                $failedCount++;
            }
        }

        // All done, return whether or not there were any failures.

        return $failedCount == 0 && $success;
    }
}