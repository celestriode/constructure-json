<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Exceptions\ConversionFailure;
use Celestriode\JsonConstructure\Exceptions\JsonTypeError;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * A data type that represents a null value. Try to avoid using this for expected structures;
 * rather, use the nullable() method on other data types. This class is primarily to represent
 * inputs, where the intended data type from the user cannot be known since they used null.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonNull extends AbstractJsonPrimitive
{
    /**
     * Returns whether or not this structure is nullable.
     *
     * @return boolean
     */
    public function isNullable(): bool
    {
        return true;
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
        // If the input is not null, throw an error.

        if ($input !== null) {

            throw new JsonTypeError("Input must be null.");
        }

        // Otherwise add it as normal.

        return parent::setValue($input);
    }

    /**
     * Takes in another structure and compares its structure to this one.
     *
     * In this case, it checks to ensure the input is also a NULL type,
     * rather than, for example, a string type that happens to be null.
     *
     * @param AbstractConstructure $constructure
     * @param StructureInterface $other
     * @return boolean
     * @throws ConversionFailure
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof AbstractJsonStructure)) {

            throw new ConversionFailure("Cannot compare non-JSON structures.");
        }

        return parent::compare($constructure, $other) && $this->typesMatch($other);
    }

    /**
     * Determines whether or not another structure is of the same type as this one.
     *
     * @param self $other The other structure to compare with.
     * @return boolean
     */
    public function typesMatch(AbstractJsonStructure $other): bool
    {
        return $other instanceof $this && $other->getValue() === null;
    }

    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return "null";
    }

}