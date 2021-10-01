<?php namespace Celestriode\JsonConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\JsonConstructure\Structures\Types\AbstractJsonParent;
use Celestriode\JsonConstructure\Structures\Types\JsonArray;

/**
 * An audit that takes in a number range (where the bounds are optional) plus whether or not the range is inclusive, and
 * returns whether or not the number of elements in the input array matches the range. Note that this applies to both
 * arrays and objects; arrays will count the elements, objects will count the fields.
 *
 * For a mix of inclusive and exclusive range (e.g. (3, 6]), use multiple of this audit. For example, the following set
 * of audits would be 30 (exclusive) to 40 (inclusive):
 *
 * addAudit(new HasNElements(30, null, false))->addAudit(new HasNElements(null, 40, true))
 *
 * @package Celestriode\JsonConstructure\Context\Audits
 */
class HasNElements extends AbstractParentAudit
{
    public const OUT_OF_RANGE = '7fe82f82-1886-4a23-a035-4e001b52ff8a';

    /**
     * @var int|null The minimum number of elements required. Can be null to indicate no minimum.
     */
    private $min;

    /**
     * @var int|null The maximum number of elements allowed. Can be null to indicate no maximum.
     */
    private $max;

    /**
     * @var bool Whether or not the range is inclusive.
     */
    private $inclusive;

    public function __construct(int $min = null, int $max = null, bool $inclusive = true)
    {
        $this->min = $min;
        $this->max = $max;
        $this->inclusive = $inclusive;
    }

    /**
     * @inheritDoc
     */
    protected function auditParent(AbstractConstructure $constructure, AbstractJsonParent $input, AbstractJsonParent $expected): bool
    {
        $count = count($input->getChildren());


        // Check if the number of elements in the input array are within the acceptable range.

        if ($this->inclusive) {

            if (($this->min !== null && $count < $this->min) || ($this->max !== null && $count > $this->max)) {


                $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $count, $this, $input, $expected);

                return false;
            }
        } else {

            if (($this->min !== null && $count <= $this->min) || ($this->max !== null && $count >= $this->max)) {

                $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $count, $this, $input, $expected);

                return false;
            }
        }

        // No issues, return true.

        return true;
    }

    /**
     * Returns the minimum number of elements required, or null if no minimum.
     *
     * @return int
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * Returns the maximum number of elements allowed, or null if no maximum.
     *
     * @return int
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * Returns whether or not the range is inclusive.
     *
     * @return bool
     */
    public function isInclusive(): bool
    {
        return $this->inclusive;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'has_n_elements';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return self::getName() . '{min=' . ($this->getMin() ?? 'null') . ',max=' . ($this->getMax() ?? 'null') . ',inclusive=' . ($this->isInclusive() ? 'true' : 'false') . '}';
    }
}