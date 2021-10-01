<?php namespace Celestriode\JsonConstructure\Structures;

use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\AbstractStructure;
use Celestriode\JsonConstructure\Exceptions\InvalidUUID;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonParent;
use Ramsey\Uuid\UuidInterface;

/**
 * The basis for all JSON structures.
 *
 * @package Celestriode\JsonConstructure\Structures
 */
abstract class AbstractJsonStructure extends AbstractStructure
{
    /**
     * @var array A mapping of UUID to AbstractJsonStructure, which can be optionally used to make it easier to access a
     * parent while building an expected tree structure.
     */
    private static $uuids = [];

    /**
     * @var AbstractJsonParent The parent of this structure, if applicable. A parent is generally an object or array.
     */
    protected $parent;

    /**
     * @var string The key that this structure owns within the parent.
     */
    protected $key;

    /**
     * @var int The index within the array, provided that the parent is an array. -1 means not applicable.
     */
    protected $index = -1;

    /**
     * Whether or not this JSON structure is allowed to be null. This differs from JsonNull in
     * that the expected type is a string but it can be null. JsonNull is generally only used
     * with inputs that are null, since it would not be possible to know what type the user
     * intended it to be.
     *
     * @var boolean
     */
    protected $nullable = false;

    /**
     * Whether or not this structure must exist, typically in terms of fields in an object.
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * The UUID of this structure object, if defined.
     *
     * @var UuidInterface
     */
    protected $uuid;

    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    abstract public function getTypeName(): string;

    /**
     * Returns whether or not the structures are equal. Just checks the inputs.
     *
     * @param self $other The other structure to compare with.
     * @return boolean
     */
    public function equals(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }

    /**
     * Determines whether or not another structure is of the same type as this one.
     *
     * @param self $other The other structure to compare with.
     * @return boolean
     */
    public function typesMatch(self $other): bool
    {
        return $other instanceof $this;
    }

    /**
     * Sets the parent of this structure, implying that this structure belongs to an object or array.
     *
     * @param AbstractJsonParent $parent The parent of this structure.
     * @param string|null $key
     * @return void
     */
    public function setParent(AbstractJsonParent $parent, string $key = null): void
    {
        $this->parent = $parent;
        $this->setKey($key);
    }

    /**
     * Returns the parent of this structure, if it exists.
     * 
     * @return AbstractJsonParent|null
     */
    public function getParent(): ?AbstractJsonParent
    {
        return $this->parent;
    }

    /**
     * Sets the element's index within the parent array, assuming the parent is an array.
     *
     * @param int $index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * Returns the index that this element appears within the parent array, if the parent is an array.
     *
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Sets the key for the structure. Primarily used when the parent is an object.
     *
     * @param string|null $key
     */
    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    /**
     * Returns the key for the structure, if set. Should be set when there is a parent.
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Marks this structure as required or not. Used with audits.
     *
     * @param boolean $required True if required, false if not.
     * @return self
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Returns whether or not this structure is required.
     *
     * @return boolean
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Marks this structure as nullable or not; that is, if the input can be null.
     *
     * @param boolean $nullable True if nullable, false if not.
     * @return self
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * Returns whether or not this structure is nullable.
     *
     * @return boolean
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Returns the input of this structure as a string, with an option to run the input
     * through some undetermined prettifying function.
     *
     * @param PrettifierInterface|null $prettifier The class to prettify the input with, if desired.
     * @return string
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        // If there's a prettifer, return its result instead.

        if ($prettifier !== null) {

            return $prettifier->prettify($this);
        }

        // Otherwise do a pure json_encode on the value.

        return json_encode($this->getValue());
    }

    /**
     * Generates a simple JSON path to this element based on its parents.
     *
     * @return string
     */
    public function toPath(): string
    {
        $str = '';

        if ($this->getKey() !== null) {

            if ($this->getParent() !== null && $this->getParent()->getParent() !== null) {

                $str = '.' . $this->getKey();
            } else {

                $str = $this->getKey();
            }
        } else if ($this->getIndex() !== -1) {
            $str = '[' . $this->getIndex() . ']' . $str;
        }

        if ($this->getParent() !== null) {

            $str = $this->getParent()->toPath() . $str;
        }

        return $str;
    }

    /**
     * Applies a UUID to this interface to be used by another structure later.
     *
     * @param UuidInterface $uuid The UUID of this structure.
     * @return self
     */
    public function setUUID(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;
        self::$uuids[$uuid->toString()] = $this;

        return $this;
    }

    /**
     * Returns the UUID of this structure, if it exists.
     *
     * @return UuidInterface|null
     */
    public function getUUID(): ?UuidInterface
    {
        return $this->uuid;
    }

    /**
     * Returns a structure based on the input UUID, should it exist.
     *
     * @param UuidInterface $uuid The UUID of the structure to return.
     * @return self
     * @throws InvalidUUID
     */
    public static function getFromUUID(UuidInterface $uuid): self
    {
        if (!isset(self::$uuids[$uuid->toString()])) {

            throw new InvalidUUID("Structure with UUID '{$uuid->toString()}' not found.");
        }

        return self::$uuids[$uuid->toString()];
    }
}