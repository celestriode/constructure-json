<?php namespace Celestriode\JsonConstructure;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Exceptions\ConversionFailureException;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Exceptions\ConversionFailure;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;
use Celestriode\JsonConstructure\Structures\Types\JsonArray;
use Celestriode\JsonConstructure\Structures\Types\JsonObject;
use Celestriode\JsonConstructure\Utils\Json;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use stdClass;

/**
 * The constructure for JSON structures.
 */
class JsonConstructure extends AbstractConstructure
{
    /**
     * Transforms an input into an AbstractJsonStructure.
     *
     * @param string $input The input to transform. With JSON, this must be a well-formed JSON string.
     * @return StructureInterface
     * @throws ConversionFailureException
     */
    public function toStructure($input): StructureInterface
    {
        if (!is_string($input)) {

            throw new ConversionFailureException("Raw input must be a string.");
        }

        try {

            $parser = new JsonParser();
            $json = $parser->parse($input, JsonParser::DETECT_KEY_CONFLICTS);

            return $this->transformData($json);
        } catch (ParsingException $e) {

            throw new ConversionFailureException('Parsing failed: ' . $e->getMessage());
        } catch (ConversionFailure $e) {

            throw new ConversionFailureException('Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Takes in some data type and turns it into a JSON constructure structure.
     *
     * @param mixed $data The data itself.
     * @return AbstractJsonStructure
     * @throws ConversionFailure
     */
    protected function transformData($data): AbstractJsonStructure
    {
        // Handle primitives.

        if ($data === null || is_string($data) || is_integer($data) || is_double($data) || is_bool($data)) {

            return $this->transformPrimitive($data);
        }

        // Handle objects.

        if ($data instanceof stdClass) {

            return $this->transformObject($data);
        }

        // Handle arrays.

        if (is_array($data)) {

            return $this->transformArray($data);
        }

        // Unknown data type.

        throw new ConversionFailure("Unknown JSON data type '" . get_class($data) . "'.");
    }

    /**
     * Takes in a primitive value and attempts to transform it into a JSON constructure primitive type.
     *
     * @param string|boolean|int|float|null $primitive
     * @return AbstractJsonPrimitive
     * @throws ConversionFailure
     */
    protected function transformPrimitive($primitive): AbstractJsonPrimitive
    {
        // JSON boolean.

        if (is_bool($primitive)) {

            return Json::boolean($primitive);
        }

        // JSON string.

        if (is_string($primitive)) {

            return Json::string($primitive);
        }

        // JSON integer.

        if (is_integer($primitive)) {

            return Json::integer($primitive);
        }

        // JSON boolean.

        if (is_double($primitive)) {

            return Json::double($primitive);
        }

        // JSON null.

        if ($primitive === null) {

            return Json::null();
        }

        // Unknown data type.

        throw new ConversionFailure("Unknown JSON primitive type '" . get_class($primitive) . "'.");
    }

    /**
     * Takes in an object and attempts to transform it into a JSON constructure array.
     *
     * @param stdClass $object The raw object to transform.
     * @return JsonObject
     * @throws ConversionFailure
     */
    protected function transformObject(stdClass $object): JsonObject
    {
        // Set up the object.

        $json = Json::object($object);

        // Cycle through fields in the input.

        foreach ($object as $key => $value) {

            // Add the child to the object.

            $json->addChild($key, $this->transformData($value));
        }

        // Return the completed object.

        return $json;
    }

    /**
     * Takes in an array and attempts to transform it into a JSON constructure array.
     *
     * @param array $array The raw array to transform.
     * @return JsonArray
     * @throws ConversionFailure
     */
    protected function transformArray(array $array): JsonArray
    {
        // Set up the array.

        $json = Json::array($array);

        // Cycle through elements in the input.

        foreach ($array as $element) {

            $json->addElement($this->transformData($element));
        }

        // Return the completed array.

        return $json;
    }
}