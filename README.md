# Constructure: JSON

An implementation of [Constructure](https://github.com/celestriode/constructure) for the [JSON](https://json.org) format. In short, this can be used to verify a JSON structure that a user may submit.

```
composer require celestriode/constructure-json
```

# Getting started

The first thing needed is a new `JsonConstructure` object. This object will hold an event handler and any global audits. It will convert a well-formed JSON string into a valid constructure structure to be used for comparison. It can also initiate the comparison and return success.

```php
$constructure = new JsonConstructure(new EventHandler(), new TypesMatch());
```

An event handler holds events that can be triggered through various means. An audit is a requirement that a particular piece of the JSON input structure must pass, while global audits are checked against *all* nested fields or elements in the JSON input.

Events and audits are described later on. For now, a brand new event handler will do just fine. The `TypesMatch` audit supplied by the library will compare the data types of the input structures with the expected structure to make sure that they match. The general usage of the library is useless without it, but sometimes you may not care about the data types.

## Converting raw input

The assumption of this library is that stringified JSON will be the raw input. Such input can be converted using the `JsonConstructure.toStructure()` method:

```php
$raw = '{"test": "hello"}';

$input = $constructure->toStructure($raw);
```

The input will be built upon JSON objects as used by the library, allowing comparison between it and an expected structure.

## Building an expected structure

Before beginning comparison, an expected structure must be built using the various tools provided by this library. This is where the bulk of your work will be, involving building the structure, creating audits, and creating events.

### Structure

The root of the structure can be any JSON data type. For example, the simplest expected structure could be checking if the input is a non-null string:

```php
$expected = Json::string();
```

Then the user input could be built up as any of the following:

```php
$raw1 = '4';           // Invalid, not a string.
$raw2 = null;          // Invalid, is null.
$raw3 = '"Hello"';     // Valid, a string with value "hello".
```

And then the input can be compared with the expected structure, returning true or false depending on its success.

```php
$constructure->validate($constructure->toStructure($raw1), $expected); // false
$constructure->validate($constructure->toStructure($raw2), $expected); // false
$constructure->validate($constructure->toStructure($raw3), $expected); // true
```

----

Of course, checking for a primitive type as the root using this library is quite overkill. The purpose is for more complex structures. The `nullable()` option allows an input structure to be null. As well, an object can make use of the `required()` option to differentiate between optional and required fields. Options can be chained for ease of creation.

```php
$expected = Json::object()
    ->addChild("time", Json::integer()->required()->nullable())
    ->addChild("event", Json::object()
        ->addChild("name", Json::string()->required()));
```

The expected structure would be one that has an object as the root. Within the object is a required `time` field that must be an integer or null. Alongside it is an optional `event` object. When that object is specified, within it must be a string field called `name`.

```php
// Valid, does not need optional "event" object.

$raw1 = '{"time": null}';

// Invalid, missing required "time" integer or null.

$raw2 = '{"event": {"name": ""}}';

// Invalid, missing the required nested "name" string.

$raw3 = '{"time": 1, "event": {}}';
```

### Audits

An audit is a custom check that you can create to fulfill your own structural requirements. If, for example, you want to restrict a string to only allowing certain values, then you'll need an audit. A handful of default audits are provided with this library to cover some of the more basic needs.

One of the default audits is `HasValue`. It can be used with any primitive data type to restrict the input to specific values.

```php
$expected = Json::object()
    ->addChild("method", Json::string()->addAudit(new HasValue("get", "post")));
```

An audit was added to the `method` string to restrict its values to "get" and "post".

```php
$raw1 = '{"method": "get"}'; // true.
$raw2 = '{"method": "postt"}'; // false (there is a typo).
$raw3 = '{}'; // true, "method" is not required.
```

### Events

By default, the feedback given from validating the input with the expected structure is simply true or false for the entire structure. If you want to provide far more feedback, or do something else in response to structural issues, then you'll need to use events.

An event is triggered by name under certain circumstances. The `HasValue` audit will trigger an event named `53fb094f-62ca-4853-9d57-9b100e22eb52` (accessed more easily with `HasValue::INVALID_VALUE`) if the input's value is not one of the required values.

Any events should be added to the constructure object's event handler. It takes a name and a closure, where the inputs to the closure depend on the event being triggered. When you're triggering events in your own custom audits, be sure to be consistent with the arguments you give it.

```php
$event = function (HasValues $hasValueAudit, AbstractJsonPrimitive $input, AbstractJsonPrimitive $expected) {

        echo "Unexpected value '{$input->getString()}', must be one of: " . implode(", ", $hasValueAudit->getValues());
}

$constructure->getEventHandler()->addEvent(HasValue::INVALID_VALUE, $event);
```

Now whenever the `HasValue::INVALID_VALUE` event is triggered, it will echo out its message.

```php
$raw = '{"method": "postt"}';
```

> Unexpected value 'postt', must be one of: get, post

# Structures

All structures that extend `AbstractJsonStructure` have access to the following methods.

| `AbstractStructure` Methods | Description |
| - | - |
| `getValue()` | Returns the value associated with the structure. Usually null for expected structures. Automatically populated when converting stringified JSON to a structure. Primarily used with audits to verify input. |
| `setValue($value)` | Sets the raw value of the structure. |
| `addAudit(AuditInterface $audit)` | Adds an audit to the structure. Used with expected structures to further verify the input. |
| `addAudits(AuditInterface ...$audits)` | adds multiple audits. |
| `toString(callable $prettifier = null)` | Transforms the structure into a string. When calling this at the root of the input structure, it will effectively return what the raw stringified JSON was before converting it. Useful for pinpointing where a problem is happening. When a closure is given, the string is passed to it with the intention of prettifying it. |
| `getAudits()` | Returns all audits associated with the structure. |
| `compare(AbstractConstructure $constructure, StructureInterface $input)` | Takes in the base constructure object and the transformed input and verifies that the input matches the expected structure. You should avoid calling this method; instead, rely on calling `validate()` from the constructure object. |
| **`AbstractJsonStructure` Methods** | **Description** |
| `getTypeName()` | Returns the data type as a user-friendly string. Can be useful for user feedback. |
| `equals(AbstractJsonStructure $other)` | Returns true if the other structure's value is equal to the current structure's value. |
| `typesMatch(AbstractJsonStructure $other)` | Returns true if the data type of the other structure matches. In the case of `JsonMixed`, checks if the other structure matches any one of the acceptable types. |
| `setParent(AbstractJsonParent $parent, string $key = null)` | Sets the parent of the structure to the given parent. If a key is provided, also sets the structure's key, which can be useful for user feedback. |
| `getParent()` | Returns the structure's parent, if existent. |
| `getKey()` | Returns the key of the structure, if existent. |
| `isRequired()` | Returns whether or not the structure is required. |
| `isNullable()` | Returns whether or not the structure is nullable. |
| **Chainable options** | **Description** |
| `required(bool $required = true)` | Marks the structure as required. This option is checked by objects to ensure it contains a required field. |
| `nullable(bool $nullable = true)` | Marks the structure as nullable, allowing inputs to be null. |
| `setUUID(UuidInterface $uuid)` | Sets an optional UUID for the structure. For more complex structures, this can be useful for redirecting (see `Json::redirect()`). |

## Structure types

JSON data types can be added to a structure using the `Utils\Json` utility. While most of these types accept a value, expected structures do not require a value. Unless you are building an input structure by hand, you do not need to supply a value.

| Type | Description |
| - | - |
| `Json::boolean()` | Creates a boolean. |
| `Json::integer()` | Creates an integer. |
| `Json::double()` | Creates a double. |
| `Json::number()` | Creates a mixed type, accepting both integers and doubles. |
| `Json::scalar()` | Creates a mixed type, accepting booleans, integers, and doubles. |
| `Json::string()` | Creates a string. |
| `Json::primitive()` | Creates a mixed type, accepting booleans, integers, doubles, and strings. |
| `Json::object()` | Creates an object. |
| `Json::array()` | Creates an array. |
| `Json::null()` | Creates a null type. |
| `Json::any()` | Creates a mixed type that accepts booleans, integers, doubles, strings, objects, arrays, and nulls. |
| `Json::mixed()` | Creates a custom mixed type. Use `addType()` to add any of the types listed in this table. |
| `Json::redirect(UuidInterface $uuid)` | Returns a previously-created structure based on a UUID. |

# Primitive structures

All primitive data types (booleans, integers, doubles, strings, and nulls) extend `AbstractJsonPrimitive`, which provides access to the following methods.

| Method | Description |
| - | - |
| `getBoolean()` | Returns the value as a boolean. |
| `getInteger()` | Returns the value as an integer. |
| `getDouble()` | Returns the value as a double. |
| `getString()` | Returns the value as a string. |

In the case of the `JsonString` structure, `getBoolean()` returns whether or not the string is empty. The `getInteger()/getDouble()` methods depend on the value of the string: if the value is strictly numeric, it will return that value as an integer/double. Otherwise, it will return the length of the string.

# Parent structures

All data types that have some form of children (objects and arrays) extend `AbstractJsonParent`, which provides access to the following methods.

| Method | Description |
| - | - |
| `addChild(string $key = null)` | Adds a child with the given key to the list of children as an associative array (used for JSON objects). If no key is provided, the list of children will be a simple flat array (used for JSON arrays). |
| `getChildren()` | Returns the array of children and, if applicable, their keys. |
| `getChild(string $key)` | Returns a specific child based on the key. If no such child exists, returns null. |
| `getKeys()` | Returns all keys associated with the children. String keys indicate a JSON object while numeric keys indicate a JSON array. |

If an object in an expected structure adds a child without a key, that child is considered to be a placeholder where the key in the input can be anything.

# Object Structures

All `JsonObject` structures have access to the following methods.

| Method | Description |
| - | - |
| `strictKeys(bool $strict = true)` | Enabled by default. Checks the input for keys that the expected structure does not expect. Useful if the user has a typo in a key name. |
| `addExpectedkey(string $key)` | By default, only the keys specified directly within the expected object structure will be used to verify unexpected keys. Since this check occurs after all audits within the object and its children run, those audits can add expected keys to dynamically change the check. |
| `addExpectedKeys(string ...$keys)` | Adds multiple expected keys. |
| `removeExpectedKey(string $key)` | Removes an expected key from the list. Does not modify the list of default expected keys. |
| `removeExpectedKeys(string ...$keys)` | Removes multiple expected keys. |
| `getExpectedKeys()` | Returns all additionally-added expected keys. Use `getKeys()` to get a list of default expected keys as defined by the expected object structure. |

As objects are also parents, they have access to parent methods for adding children.

```php
$expected = Json::object()
    ->addChild("first", Json::string())
    ->addChild("second", Json::number());

$raw1 = '{"first": "hello", "second": 40}'; // Passes.
$raw2 = '{"first": 40, "second": "hello"}'; // Both fail.
```

## Placeholders

When adding a child without a key (e.g. `addChild(null, Json::string())`) to an expected object structure, the input can contain any key so long as it perfectly matches the child's structure. During the checks, events are not immediately triggered. If there is no perfect match, all events that had occurred during those checks will be triggered.

```php
$expected = Json::object()->addChild(null, Json::string());

$raw1 = '{"hello": "yes", "goodbye": "no}'; // Both "hello" and "goodbye" pass.
$raw2 = '{"hello", "yes", "goodbye", 3}';   // "hello" passes but "goodbye" fails.
```

You can have multiple placeholders with varying structures. Be careful when doing this though, as the first structure the input matches will prevent it from checking the rest, even if there is another perfect match.

# Array Structures

All `JsonArray` structures have access to the following methods.

| Method | Description |
| - | - |
| `addElement(AbstractJsonStructure $element)` | Adds a single element to the children of the array. Essentially just an alias for `addChild(null, $element)`. |
| `addElements(AbstractJsonStructure ...$elements)` | Adds multiple elements to the children of the array. |

If an expected array structure has multiple children, each element of the input will be compared to each one until there is a perfect match (similar to object placeholders).

```php
$expected = Json::array()->addElements(Json::string(), Json::boolean());

$raw1 = '[true, true, "hello", false, "goodbye"]'; // All elements pass.
$raw2 = '[3, true, "hello", false]';               // Element at index 0 fails.
```

# Audits

The library comes with a handful of audits that can prove useful.

| Name | Description |
| - | - |
| `HasValue(...$values)` | Takes in values that are accepted for primitive structures. If the input does not use one of them, the audit fails. |
| `ChildHasValue(string $key, ...$values)` | Checks if an object has a primitive child with the specified key and if that child uses one of the specified values. If not, the audit fails. |
| `SiblingHasValue(string $key, ...$values)` | Checks if a field has a sibling (another field within the same object) of the specified key and if that sibling uses one of the specified values. If not, the audit fails. |
| `NumberRange(int $min = null, int $max = null)` | Checks if the integer/double input has a value between the min and max (inclusive). The audit fails if not. If either min or max are null, there is no bound on that end. |
| `StringLength(int $min = null, int $max = null)` | Checks if the length of the string input is between the min and max (inclusive). The audit fails if not. If either min or max are null, there is no bound on that end. |
| `TypesMatch()` | Ensures that the data types of the input and expected structure match. If the input is null and the expected structure is nullable, the audit passes. If the data types otherwise do not match, the audit fails. |
| `InputsMatch()` | Simply checks if the values of the input and expected structures match. This is generally not used as expected structures don't often use single values, and one could use `HasValue` instead. |
| `InclusiveFields(string ...$keys)` | Checks if the input object has children with the specified keys. All keys must exist in the input object. |
| `ExclusiveFields(string ...$keys)` | Checks the input object for having the specified keys. If it contains more than one of those keys, the audit fails. Chain the `needsOne()` method to the audit to require exactly one of the specified keys to exist. |
| `Branch(string $name, AbstractJsonStructure $branch, AuditInterface ...$predicates)` | Allows the expected structure to change based on the success of supplied predicates (which are audits that will not trigger events). If all predicates pass, then the structure attached to the branch will be compared with the input. |

## Branching

Branching is a technique to adapt the expected structure based on the input. For example, if a string in an object had a particular value, then a whole bunch of other fields should become available for validating. Branching is performed using the pre-packaged `Branch` audit, but you can create your own with specific functionality too.

In the following example, Branch A becomes available if the `method` field has the value "first", while Branch B becomes available if it instead has the value "second". The root object only validates the "method" field, but once a branch becomes available, the structure associated with the branch effectively merges with the object.

```php
$branchA = new Branch("Branch A", Json::object()->addChild("with", Json::boolean()->required()), new ChildHasValue("method", "first"))

$branchB = new Branch("Branch B", Json::object()->addChild("next", Json::boolean()->required()), new ChildHasValue("method", "second"))

$expected = Json::object()
    ->addChild("method", Json::string())
    ->addAudits($branchA, $branchB)

$raw1 = '{"method": "first", "with": true}';   // Passes.
$raw2 = '{"method": "second", "next": false}'; // Passes.
$raw3 = '{"method": "third"}';                 // Passes because there is no HasValue audit on the "method" string itself.
$raw4 = '{}';                                  // Passes because the "method" string is not required.
$raw5 = '{"with": 4}';                         // Fails because "with" was not expected.
$raw6 = '{"method": "first", "with": 4}';      // Fails because "with" must be a boolean.
$raw7 = '{"method": "first"}';                 // Fails because "with" is required.
```

## Creating your own JSON audits

All `JsonConstructure`-focused audits should begin by extending the `AbstractJsonAudit` class, which verifies the structures passed to it as being JSON. You will need two methods: `auditJson()` and `getName()`.

```php
class CustomAudit extends AbstractJsonAudit
{
    protected function auditJson(AbstractConstructure $constructure, AbstractJsonStructure $input, AbstractJsonStructure $expected): bool
    {
        ...
    }

    public static function getName(): string
    {
        return "custom_audit";
    }
}
```

The `getName()` method returns a more user-friendly name of the audit, which can be included in feedback to the user if desired. It serves no other defined purpose.

With the `auditJson()` method, you are given the constructure object that is being used to validate the input. You can use it to trigger events based on what happens within the audit. Aside from that, you are given the input structure itself, whose input you can validate with the audit, as well as the expected structure, which you can use for further control over what is expected by the input.

For example, the following audit would ensure that the string input has a length of at least 10:

```php
protected function auditJson(AbstractConstructure $constructure, AbstractJsonStructure $input, AbstractJsonStructure $expected): bool
{
    // Ensure the input and expected structures are both strings.
    // Feedback from this can be covered by the TypesMatch audit.

    if (!($input instanceof JsonString) || !($expected instanceof JsonString)) {

        return false;
    }

    // Check if the length of the string is less than 10.

    if (strlen($input->getValue()) < 10) {

        // Trigger an event. Pass in the audit, the input, and expected structure.
        // The event can do what it likes with these.

        $constructure->getEventHandler()->trigger("some unique event name", $this, $input, $expected);

        // Since the audit failed, return false.

        return false;
    }

    // The string has a length of 10 or more, so the audit passes.

    return true;
}
```

The event name should refer to some event that uniquely handles this custom audit. In these situations, you'll want to create a name that is far more likely to be unique. Using a [generated UUID](https://www.uuidgenerator.net) is a decent way to do that. Setting such a name as a constant on the class makes it easier to refer to it outside the class, without having to memorize a UUID.

```php
class CustomAudit extends AbstractJsonAudit
{
    const INVALID_VALUE = '1627ea1f-e25c-4f08-aa31-eeb0cf6bdfec';

    protected function auditJson(...): bool
    {
        ...

        $constructure->getEventHandler()->trigger(self::INVALID_VALUE, $this, $input, $expected);

        ...
    }

    ...
}
```

Using an audit in this manner isn't very helpful for more than one specific use-case. Audits should ideally be generalized to apply to many situations. For example, what if you wanted another audit to check for a string length of 15 or higher instead of only 10?

You could use the `__construct()` magic method to take in a minimum (and even a maximum) string length, and the body of the audit can be generalized using those inputs. Even further, extending other audits can be helpful for reducing code duplication. For instance, the `NumberRange` class already has inputs for a min and max in its constructor and also ensures the incoming structures are primitives. It also comes with a `withinRange(float $value): bool` method to validate any numeric value based on the min and max.

```php
class CustomAudit extends NumberRange
{
    const INVALID_VALUE = '1627ea1f-e25c-4f08-aa31-eeb0cf6bdfec';

    protected function auditPrimitive(AbstractConstructure $constructure, AbstractJsonPrimitive $input, AbstractJsonPrimitive $expected): bool
    {
        // Get the value of the input as a string and get its length.

        $length = strlen($input->getString());

        // Check the length of the string against the min and max.

        if ($this->withinRange($length)) {

            // The length is correct, so the audit passes.

            return true;
        }

        // The length is incorrect, so the audit fails.

        $constructure->getEventHandler()->trigger(self::INVALID_VALUE, $this, $input, $expected);

        return false;
    }

    ...
}
```

Which, as you may note, is practically the same as the pre-packaged `StringLength` audit.

## Abstract audit classes

There are a few higher-level abstract audit classes that you can extend to reduce the number of checks you need to write yourself.

| Class | Description |
| - | - |
| `AbstractJsonAudit` | Verifies that the incoming structures both extend `AbstractJsonStructure`. |
| `AbstractBooleanAudit` | Verifies that the incoming JSON structures both extend `JsonBoolean`. |
| `AbstractNumericAudit` | Verifies that the incoming JSON structures both extend either `JsonInteger` or `JsonDouble`. |
| `AbstractStringAudit` | Verifies that the incoming JSON structures both extend `JsonString`. |
| `AbstractObjectAudit` | Verifies that the incoming JSON structures both extend `JsonObject`. |
| `AbstractArrayAudit` | Verifies that the incoming JSON structures both extend `JsonArray`. |
| `AbstractPrimitiveAudit` | Verifies that the incoming JSON structures both extend `AbstractJsonPrimitive`. This covers all primitive types (booleans, integers, doubles, strings, nulls). |
| `AbstractParentAudit` | Verifies that the incoming JSON structures both extend `AbstractJsonParent`. This covers objects and arrays. |

# Events

An event is a custom function that is triggered under certain conditions. Events will primarily fire from failed audits. The primary event handler is stored within the constructure object. You can choose to either build the event handler prior to instantiating the constructure or build upon it after instantiating.

```php
$eventHandler = new EventHandler();
$eventHandler->addEvent(...);

$constructure = new JsonConstructure($eventHandler);
```
```php
$constructure = new JsonConstructure(new EventHandler());

$constructure->getEventHandler()->addEvent(...);
```

The former may be easier when you've got a collection of various structures available for validation.