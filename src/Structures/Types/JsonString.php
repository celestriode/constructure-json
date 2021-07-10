<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Exceptions\JsonTypeError;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Represents JSON strings, with overridden methods for transforming the input into
 * other data types.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonString extends AbstractJsonPrimitive
{
    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return "string";
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
        // If the input is not a string, throw an error.

        if ($input !== null && !is_string($input)) {

            throw new JsonTypeError("Input must be a string.");
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

            return is_string($other->getValue());
        }

        return false;
    }

    /**
     * Returns whether or not the string is empty. Default for typecasting, but
     * being explicit about the intention is helpful for porting.
     *
     * @return boolean|null
     */
    public function getBoolean(): ?bool
    {
        return !empty($this->getString());
    }

    /**
     * If numeric, returns the number. Otherwise, returns the length of the string.
     *
     * @return integer|null
     */
    public function getInteger(): ?int
    {
        if (is_numeric($this->getString())) {

            return (int)$this->getString();
        }

        return strlen($this->getString());
    }

    /**
     * If numeric, returns the number. Otherwise, returns the length of the string.
     *
     * @return float|null
     */
    public function getDouble(): ?float
    {
        if (is_numeric($this->getString())) {

            return (float)$this->getString();
        }

        return (float)strlen($this->getString());
    }
}