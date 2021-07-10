<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Exceptions\JsonTypeError;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Represents a JSON double. Fairly straight-forward.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonDouble extends AbstractJsonPrimitive
{
    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return "double";
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
        // If the input is not a float, throw an error.

        if ($input !== null && !is_float($input)) {

            throw new JsonTypeError("Input must be a float.");
        }

        // Otherwise add it as normal.

        return parent::setValue($input);
    }

    /**
     * Determines whether or not another structure is of the same type as this one.
     *
     * @param self $other The other structure to compare with.
     * @return boolean
     */
    public function typesMatch(AbstractJsonStructure $other): bool
    {
        if (parent::typesMatch($other)) {

            return is_double($other->getValue());
        }

        return false;
    }
}