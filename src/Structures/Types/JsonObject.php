<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Context\Audits\TypesMatch;
use Celestriode\JsonConstructure\Exceptions\AuditFailure;
use Celestriode\JsonConstructure\Exceptions\JsonTypeError;

/**
 * Represents a JSON object. Contains fields (children) and will therefore compare each
 * child in the input to children in the expected structure.
 * 
 * If a child is added with a null key, it will be considered a placeholder. An expected
 * object having a placeholder means that the input can have any key, as long as it still
 * matches the placeholder's type. This means you can have one placeholder per data type.
 * Use the Branch audit for more control over this.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonObject extends AbstractJsonParent
{
    const COMPARING_CHILD = '3a5b369e-2d42-4dcc-8a42-c727a2db0f23';
    const CHILD_COMPARED = '6ca3ad46-cec6-48c5-a0c4-f3583fcc7dcc';
    const PLACEHOLDERS_FAILED = 'eba7449b-9b2b-4fc0-8866-f934b2e92eed';
    const MISSING_FIELD = '3802b600-e921-4ea0-9911-9c7668d4a4c5';
    const UNEXPECTED_KEYS = '41856ae1-0daf-425c-8ed0-78692d4115e9';

    /**
     * @var bool Whether or not the object checks for unexpected keys.
     */
    protected $strictKeys = true;

    /**
     * @var array Additional keys to expect, populated by audits.
     */
    protected $expectedKeys = [];

    /**
     * @var bool If true, evaluation will fail when unexpected keys are encountered.
     */
    protected $failOnUnexpectedKeys = false;

    /**
     * Returns a friendlier name of the data structure, which can be used with errors shown
     * to the user.
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return "object";
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
        // If the input is not an object, throw an error.

        if ($input !== null && !($input instanceof \stdClass)) {

            throw new JsonTypeError("Input must be an object.");
        }

        // Otherwise add it as normal.

        return parent::setValue($input);
    }

    /**
     * Marks the object as having strict keys, which is the default.
     *
     * A JsonObject will check the input's keys to locate any keys that are unexpected. Setting
     * this option to false will disable that check. This check occurs after checking audits,
     * so you can use audits to add or remove expected keys.
     *
     * Any unexpected keys found will trigger an event.
     *
     * @param bool $strictKeys Set to false to disable strict key checking.
     * @return self
     */
    public function strictKeys(bool $strictKeys = true): self
    {
        $this->strictKeys = $strictKeys;

        return $this;
    }

    /**
     * Returns whether or not unexpected keys are checked.
     *
     * @return bool
     */
    public function hasStrictKeys(): bool
    {
        return $this->strictKeys;
    }

    /**
     * When set to true, encountering unexpected keys will cause evaluation to fail. By default, this does not occur.
     * An event is still triggered when unexpected keys are encountered; use this to overturn that.
     *
     * @param bool $failOnUnexpectedKeys Whether or not evaluation fails.
     * @return $this
     */
    public function failOnUnexpectedKeys(bool $failOnUnexpectedKeys = true): self
    {
        $this->failOnUnexpectedKeys = $failOnUnexpectedKeys;

        return $this;
    }

    /**
     * Returns whether or not the object should fail evaluation if unexpected keys are encountered.
     *
     * @return bool
     */
    public function failsOnUnexpectedKeys(): bool
    {
        return $this->failOnUnexpectedKeys;
    }

    /**
     * Adds new keys to the list of expected keys. This can be useful for audits to add
     * their own keys.
     *
     * @param string ...$expectedKeys The new keys to add.
     * @return self
     */
    public function addExpectedKeys(string ...$expectedKeys): self
    {
        foreach ($expectedKeys as $expectedKey) {

            $this->addExpectedKey($expectedKey);
        }

        return $this;
    }

    /**
     * Adds a new key to the list of expected keys. This can be useful for audits to add
     * their own keys.
     *
     * @param string $expectedKey The new key to add.
     * @return self
     */
    public function addExpectedKey(string $expectedKey): self
    {
        // Only add the key if it isn't already present.

        if (!in_array($expectedKey, $this->expectedKeys)) {

            $this->expectedKeys[] = $expectedKey;
        }

        return $this;
    }

    /**
     * Removes specific keys from the list of expected keys.
     *
     * @param string ...$expectedKeys The keys to remove.
     * @return self
     */
    public function removeExpectedKeys(string ...$expectedKeys): self
    {
        foreach ($expectedKeys as $expectedKey) {

            $this->removeExpectedKey($expectedKey);
        }

        return $this;
    }

    /**
     * Removes a specific key from the list of expected keys.
     *
     * @param string $expectedKey The key to remove.
     * @return self
     */
    public function removeExpectedKey(string $expectedKey): self
    {
        $this->expectedKeys = array_diff($this->expectedKeys, [$expectedKey]);

        return $this;
    }

    /**
     * Returns the additionally-expected keys. Merge this with obj->getKeys() to get the full
     * set, if needed.
     *
     * @return array
     */
    public function getExpectedKeys(): array
    {
        return $this->expectedKeys;
    }

    /**
     * Takes in another structure and compares its structure to this one.
     *
     * Checks audits for the object first, then compares children to expected fields. Afterwards,
     * placeholders are checked. Children added to the expected structure with a null key are
     * considered placeholders, where the input can have any key (as long as it didn't match
     * any key-specific structure previously).
     *
     * Then, required keys are checked. Finally, if strictKeys is true, it will check for any keys
     * that the input has that was not found in either the object's strictly-specified keys or in
     * the extra expectedKeys array, which can be populated with audits.
     *
     * @param AbstractConstructure $constructure
     * @param StructureInterface $other
     * @return boolean
     * @throws AuditFailure
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        // Do audits for the object itself first.

        $success = parent::compare($constructure, $other);

        // If the other is not an instance of JsonObject, then keys cannot be compared; end it here.
        
        if (!($other instanceof JsonObject)) {

            return false;
        }

        $problems = 0;

        // First check all of the fields it expects.

        $placeholders = [];

        foreach ($this->getChildren() as $key => $expectedChild) {

            // If the key is not a string, set it aside for later.

            if (!is_string($key)) {

                $placeholders[] = $expectedChild;
            }

            // If the other object has the key, evaluate it.

            else if (($otherChild = $other->getChild($key)) != null) {

                $constructure->getEventHandler()->trigger(self::COMPARING_CHILD, $key, $otherChild, $expectedChild, $other, $this);

                $result = $expectedChild->compare($constructure, $otherChild);

                $constructure->getEventHandler()->trigger(self::CHILD_COMPARED, $result, $key, $otherChild, $expectedChild, $other, $this);

                $problems = $problems + ($result ? 0 : 1);
            }
        }

        // If there were placeholders, try to match them against all the skipped children.

        if (!empty($placeholders)) {

            $skippedKeys = array_diff($other->getKeys(), $this->getExpectedKeys(), $this->getKeys());
            $this->addExpectedKeys(...$skippedKeys);
            $typesAudit = TypesMatch::get();

            // Cycle through all the skipped children and attempt to compare them to placeholders.

            foreach ($skippedKeys as $skippedKey) {

                $skippedChild = $other->getChild($skippedKey);
                $passes = false;

                // Cycle through all placeholders.

                foreach ($placeholders as $placeholder) {

                    // Capture the type audit instead of muting. Release later if there was an issue.

                    $constructure->getEventHandler()->capture();

                    // If the types match, attempt the audit and skip other placeholders. Use branches for better control.

                    if ($typesAudit->audit($constructure, $skippedChild, $placeholder)) {

                        // Clear captured events from the type audit.

                        // Attempt comparison. End it here. TODO: figure out if I want to clear() before or not.
                        
                        $passes = $placeholder->compare($constructure, $skippedChild);

                        // If the comparison succeeds, stop it here. TODO: should it continue?

                        if ($passes) {

                            break;
                        }
                    }
                }

                // If the comparison failed...

                if (!$passes) {

                    $success = false;

                    // Release any captured events from the attempts and trigger another event.
                    
                    $constructure->getEventHandler()->release();
                    $constructure->getEventHandler()->trigger(self::PLACEHOLDERS_FAILED, $skippedChild, $this);
                } else {

                    // Otherwise stop capturing.

                    $constructure->getEventHandler()->clear();
                }
            }
        }

        // Now check for missing required keys.

        foreach ($this->getChildren() as $key => $child) {

            if ($key !== null && $other->getChild($key) === null && $child->isRequired()) {

                $success = false;

                $constructure->getEventHandler()->trigger(self::MISSING_FIELD, $key, $this, $other);
            }
        }

        // If strict, check for unexpected keys.

        if ($this->hasStrictKeys()) {
            
            // If strict, check for keys that exist in the input that are not expected.

            $unexpectedKeys = array_diff($other->getKeys(), $this->getKeys(), $other->getExpectedKeys());

            if (!empty($unexpectedKeys)) {

                $constructure->getEventHandler()->trigger(self::UNEXPECTED_KEYS, $unexpectedKeys, $this, $other);

                // Only fail if set to do so.

                if ($this->failsOnUnexpectedKeys()) {

                    $success = false;
                }
            }

        }

        // All done, return whether or not there were any issues.

        return ($problems == 0 && $success);
    }
}