<?php namespace Celestriode\JsonConstructure\Utils;

use Celestriode\JsonConstructure\Exceptions\InvalidUUID;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\JsonArray;
use Celestriode\JsonConstructure\Structures\Types\JsonBoolean;
use Celestriode\JsonConstructure\Structures\Types\JsonDouble;
use Celestriode\JsonConstructure\Structures\Types\JsonInteger;
use Celestriode\JsonConstructure\Structures\Types\JsonMixed;
use Celestriode\JsonConstructure\Structures\Types\JsonNull;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;
use Celestriode\JsonConstructure\Structures\Types\JsonString;
use Ramsey\Uuid\UuidInterface;
use stdClass;

/**
 * Holds a variety of helper functions for creating new JSON object instances.
 */
class Json
{
    /**
     * Creates a new null JSON structure.
     *
     * @return JsonNull
     */
    public static function null(): JsonNull
    {
        return new JsonNull();
    }

    /**
     * Creates a new boolean JSON structure.
     *
     * @param boolean $value The value of the input.
     * @return JsonBoolean
     */
    public static function boolean(bool $value = null): JsonBoolean
    {
        return new JsonBoolean($value);
    }

    /**
     * Creates a new integer JSON structure.
     *
     * @param int|null $value The value of the input.
     * @return JsonInteger
     */
    public static function integer(int $value = null): JsonInteger
    {
        return new JsonInteger($value);
    }

    /**
     * Creates a new double JSON structure.
     *
     * @param float|null $value The value of the input.
     * @return JsonDouble
     */
    public static function double(float $value = null): JsonDouble
    {
        return new JsonDouble($value);
    }

    /**
     * Creates a new string JSON structure.
     *
     * @param string|null $value The value of the input.
     * @return JsonString
     */
    public static function string(string $value = null): JsonString
    {
        return new JsonString($value);
    }

    /**
     * Creates a new object JSON structure.
     *
     * @param stdClass|null $value The value of the input.
     * @return JsonObject
     */
    public static function object(stdClass $value = null): JsonObject
    {
        return new JsonObject($value);
    }

    /**
     * Creates a new array JSON structure.
     *
     * @param array|null $value The value of the input.
     * @return JsonArray
     */
    public static function array(array $value = null): JsonArray
    {
        return new JsonArray($value);
    }

    /**
     * Creates a new mixed JSON structure representing a double or integer.
     *
     * @param float|null $value The value of the input.
     * @return JsonMixed
     */
    public static function number(float $value = null): JsonMixed
    {
        return (new JsonMixed($value))->addType(self::integer((int)$value))->addType(self::double((float)$value));
    }

    /**
     * Creates a new mixed JSON structure representing a double, integer, or boolean.
     *
     * @param mixed $value The value of the input.
     * @return JsonMixed
     */
    public static function scalar($value = null): JsonMixed
    {
        return self::number((float)$value)->addType(self::boolean((bool)$value));
    }

    /**
     * Creates a new mixed JSON structure representing a double, integer, boolean, or string.
     *
     * @param mixed $value The value of the input.
     * @return JsonMixed
     */
    public static function primitive($value = null): JsonMixed
    {
        return self::scalar($value)->addType(self::string((string)$value));
    }

    /**
     * Returns a new mixed JSON structure representing all data types.
     *
     * @param mixed $value The value of the input.
     * @return JsonMixed
     */
    public static function any($value = null): JsonMixed
    {
        return self::scalar($value)->addType(self::null())->addType(self::object($value))->addType(self::array($value));
    }

    /**
     * Creates a new mixed JSON structure representing whatever the supplied classes are.
     *
     * @param mixed $value The value of the input.
     * @param AbstractJsonStructure ...$structures
     * @return JsonMixed
     */
    public static function mixed($value = null, AbstractJsonStructure ...$structures): JsonMixed
    {
        $mixed = new JsonMixed($value);

        foreach ($structures as $structure) {

            $mixed->addType($structure);
        }

        return $mixed;
    }

    /**
     * Returns a previously-created structure based on its UUID.
     *
     * @param UuidInterface $uuid The UUID of the structure to redirect to.
     * @return JsonBoolean
     * @throws InvalidUUID
     */
    public static function redirect(UuidInterface $uuid): AbstractJsonStructure
    {
        return AbstractJsonStructure::getFromUUID($uuid);
    }
}