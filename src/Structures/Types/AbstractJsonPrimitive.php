<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * An interface for JSON types of a simpler nature.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
abstract class AbstractJsonPrimitive extends AbstractJsonStructure
{
    /**
     * Returns the primitive's value as a boolean.
     *
     * @return boolean|null
     */
    public function getBoolean(): ?bool
    {
        return (bool)$this->getValue();
    }

    /**
     * Returns the primitive's value as an integer.
     *
     * @return integer|null
     */
    public function getInteger(): ?int
    {
        return (int)$this->getValue();
    }

    /**
     * Returns the primitive's value as a double.
     *
     * @return float|null
     */
    public function getDouble(): ?float
    {
        return (float)$this->getValue();
    }

    /**
     * Returns the primitive's value as a string.
     *
     * @return string|null
     */
    public function getString(): ?string
    {
        return (string)$this->getValue();
    }
}