<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * A parent structure is able to contain 1 or more child structures. This is primarily
 * useful for objects and arrays.
 *
 * Although array elements do not contain keys, a key must still be provided. It can be
 * helpful to use the array's key (if present) to specify that a child belongs to a
 * particular array.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
abstract class AbstractJsonParent extends AbstractJsonStructure
{
    /**
     * @var AbstractJsonStructure[] An associative array of AbstractJsonStructure objects that are children to this
     * parent. If no keys are provided, it will be a flat array.
     */
    protected $children = [];

    /**
     * Adds a child to the parent and returns the parent.
     *
     * @param string|null $key The user-friendly name of the child, which could be a field's key.
     * @param AbstractJsonStructure $child The child itself.
     * @return AbstractJsonParent
     */
    public function addChild(?string $key, AbstractJsonStructure $child): self
    {
        $child->setParent($this, $key);

        // If the key is null, this is not an associative array.

        if ($key === null) {

            $this->children[] = $child;
        } else {

            $this->children[$key] = $child;
        }

        return $this;
    }

    /**
     * Returns a child based on its name, if it exists. If it doesn't exist, returns null.
     *
     * @param string $key The name of the child to return.
     * @return AbstractJsonStructure|null
     */
    public function getChild(string $key): ?AbstractJsonStructure
    {
        return $this->children[$key] ?? null;
    }

    /**
     * Returns all children stored in this parent.
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Returns all keys.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->getChildren());
    }
}