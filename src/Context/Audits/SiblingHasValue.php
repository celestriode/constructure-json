<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;

/**
 * Checks whether or not this structure has a sibling with one of several values. The input must have a parent, the
 * parent must be a JsonObject, and the object must have a child with the given key and one of the specified values. The
 * sibling must have a primitive data type (booleans, numbers, strings).
 *
 * Extends HasValue to reduce code duplication.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class SiblingHasValue extends HasValue
{
    const NO_PARENT = '369e04c6-b140-471f-8896-1d3d75a5963f';
    const NO_SIBLING = '0b2f74eb-5567-426a-8c17-399d12ebdec5';

    /**
     * @var string The key of the sibling to check.
     */
    protected $key;

    /**
     * @param string $key The key of the sibling to check.
     * @param mixed ...$values The acceptable values for the sibling.
     */
    public function __construct(string $key, ...$values)
    {
        parent::__construct(...$values);

        $this->key = $key;
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
        // Get parent if it exists.

        $inputParent = $input->getParent();

        if ($inputParent === null) {

            $constructure->getEventHandler()->trigger(self::NO_PARENT, $constructure, $input, $expected);

            return false;
        }

        // Obtain the sibling, if it exists.

        $sibling = $inputParent->getChild($this->getKey());

        if ($sibling === null) {

            $constructure->getEventHandler()->trigger(self::NO_SIBLING, $constructure, $input, $expected);

            return false;
        }

        // And now compare the sibling to the expected values.

        return parent::auditJson($constructure, $sibling, $expected);
    }

    /**
     * Returns the key of the sibling that must exist.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "sibling_has_value";
    }
}