<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Events\EventHandler;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Context\Audits\TypesMatch;
use Celestriode\JsonConstructure\Exceptions\AuditFailure;
use Celestriode\JsonConstructure\Exceptions\JsonTypeError;
use Celestriode\JsonConstructure\JsonConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * A JSON class that is a container for multiple other JSON classes. Use this to accept a variety of data types.
 * For example, if an input can be either an integer or a double, this mixed class can contain both.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonMixed extends AbstractJsonStructure
{
    /**
     * @var AbstractJsonStructure[] A list of acceptable AbstractJsonStructure objects that are used for comparison.
     */
    protected $types = [];

    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    public function getTypeName(): string
    {
        $buffer = [];

        foreach ($this->types as $type) {

            $buffer[] = $type->getTypeName();
        }

        return implode(", ", $buffer);
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
        // If the input is not any acceptable type, throw an error. Not that the value of mixed types are used.

        if ($input !== null &&
            (
                !is_bool($input)
                && !is_int($input)
                && !is_float($input)
                && !is_string($input)
                && !is_array($input)
                && !($input instanceof \stdClass))
        ) {

            throw new JsonTypeError("Input must be one of: bool, int, float, string, array, object.");
        }

        // Otherwise add it as normal.

        return parent::setValue($input);
    }

    /**
     * Adds an acceptable data type to the mix.
     *
     * @param AbstractJsonStructure $type The type to add.
     * @return self
     */
    public function addType(AbstractJsonStructure $type): self
    {
        $this->types[] = $type;

        // If the mixed object is nullable, then all its children must be too.

        if ($this->isNullable()) {

            $type->nullable();
        }

        return $this;
    }

    /**
     * Marks this structure as nullable or not; that is, if the input can be null.
     * 
     * @param boolean $nullable True if nullable, false if not.
     * @return self
     */
    public function nullable(bool $nullable = true): AbstractJsonStructure
    {
        parent::nullable($nullable);

        // Also marks all acceptable types as nullable or not.

        foreach ($this->types as $type) {

            $type->nullable($nullable);
        }
        
        return $this;
    }

    /**
     * Returns whether or not this structure is nullable.
     *
     * @return boolean
     */
    public function isNullable(): bool
    {
        // If the mixed type is itself utterly nullable, then return true.

        if (parent::isNullable()) {

            return true;
        }

        // Otherwise, cycle through each of the nested types to see if any are nullable.

        foreach ($this->types as $type) {

            if ($type->isNullable()) {

                return true;
            }
        }

        // No nested types are nullable, return false.

        return false;
    }

    /**
     * Takes in another structure and compares its structure to this one.
     *
     * In the case of a mixed structure, any one of the acceptable types can match.
     *
     * @param AbstractConstructure $constructure
     * @param StructureInterface $other
     * @return boolean
     * @throws AuditFailure
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        // Perform global audits and other audits that encapsulate the entire mixed structure.

        $success = parent::compare($constructure, $other);

        // Prepare silent type-matching audits.

        $typesAudit = TypesMatch::get();
        $constructure->getEventHandler()->mute();

        // Cycle through all acceptable types.

        foreach ($this->types as $type) {

            // If the input matches an expected type, use it.

            if ($typesAudit->audit($constructure, $other, $type)) {

                $constructure->getEventHandler()->unmute();

                // Return whether or not the initial audits succeeded and if the type comparison succeeds.

                return $success && $type->compare($constructure, $other);
            }
        }

        // No acceptable types matched, unmute and return false.

        $constructure->getEventHandler()->unmute();

        return false;
    }

    /**
     * Determines whether or not another structure is of the same type as this one.
     *
     * @param self $other The other structure to compare with.
     * @return boolean
     * @throws AuditFailure
     */
    public function typesMatch(AbstractJsonStructure $other): bool
    {
        // Cycle through every acceptable type. If one matches, then it's all good.

        foreach ($this->types as $type) {

            // Cycle event handling while doing a silent audit.

            $typesAudit = TypesMatch::get();

            $result = $typesAudit->audit(new JsonConstructure(new EventHandler()), $other, $type);

            // If there was a match, return true.

            if ($result) {

                return true;
            }
        }

        // No type matched the other structure's type.
        
        return false;
    }

    /**
     * Returns whether or not the structures are equal.
     *
     * @param self $other The other structure to compare with.
     * @return boolean
     */
    public function equals(AbstractJsonStructure $other): bool
    {
        // If the other type is also mixed, then every subtype must match.

        // TODO: this.

        // Check if any type is equal to the specified type.

        foreach ($this->types as $type) { // TODO: infinite loop with $other as JsonMixed?

            if ($type->equals($other)) {

                return true;
            }
        }

        // Otherwise there was no match, return false.

        return false;
    }
}