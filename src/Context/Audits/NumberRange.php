<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonPrimitive;

/**
 * Checks if the input is a number between a minimum and maximum. Inclusive by default. If either a minimum or a maximum
 * are null, then there is no lower or upper bound respectively.
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class NumberRange extends AbstractPrimitiveAudit
{
    const OUT_OF_RANGE = '15842e25-9b89-4add-8cf8-64f5a4a29c13';

    /**
     * @var float|null The minimum allowed value. Null for no minimum.
     */
    protected $min;

    /**
     * @var float|null The maximum allowed value. Null for no maximum.
     */
    protected $max;

    /**
     * @var bool Whether or not the range is exclusive instead of inclusive.
     */
    protected $exclusive;

    /**
     * @param float|null $min The minimum allowed value. Null for no minimum.
     * @param float|null $max The maximum allowed value. Null for no maximum.
     * @param bool $exclusive Whether or not the range is exclusive instead of inclusive.
     */
    public function __construct(float $min = null, float $max = null, bool $exclusive = false)
    {
        $this->min = $min;
        $this->max = $max;
        $this->exclusive = $exclusive;
    }

    /**
     * Audits two primitive structures.
     *
     * @param AbstractConstructure $constructure The base constructure object, which holds the event handler.
     * @param AbstractJsonPrimitive $input The input to be compared with the expected structure.
     * @param AbstractJsonPrimitive $expected The expected structure that the input should adhere to.
     * @return boolean
     */
    protected function auditPrimitive(AbstractConstructure $constructure, AbstractJsonPrimitive $input, AbstractJsonPrimitive $expected): bool
    {
        $value = $input->getDouble();

        if ($this->withinRange($value)) {

            return true;
        }

        $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $this, $input, $expected);

        return false;
    }

    /**
     * Returns whether or not the input is within the expected range. Inclusive by default.
     *
     * @param float $value The value to check if it is within range of this instance's min and max.
     * @return bool
     */
    protected function withinRange(float $value): bool
    {
        // If exclusive, do not include endpoints.

        if ($this->isExclusive()) {

            return ($this->getMin() == null || $value > $this->getMin()) && ($this->getMax() == null || $value < $this->getMax());
        }

        // Inclusive, endpoints.

        return ($this->getMin() == null || ($value >= $this->getMin())) && ($this->getMax() == null || ($value <= $this->getMax()));
    }

    /**
     * Returns the minimum value accepted, or null if no bound.
     *
     * @return float|null
     */
    public function getMin(): ?float
    {
        return $this->min;
    }

    /**
     * Returns the maximum value accepted, or null if no bound.
     *
     * @return float|null
     */
    public function getMax(): ?float
    {
        return $this->max;
    }

    /**
     * Returns whether or not the range is exclusive. False means it is inclusive.
     *
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * The user-friendly name for the audit, which can be displayed to the user or used when logged.
     *
     * @return string
     */
    public static function getName(): string
    {
        return "number_range";
    }
}