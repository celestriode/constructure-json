<?php namespace Celestriode\JsonConstructure\Structures\Types;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\JsonConstructure\Exceptions\InvalidUUID;
use Celestriode\JsonConstructure\Structures\AbstractJsonStructure;
use Ramsey\Uuid\UuidInterface;

/**
 * A container for redirection. Assign a UUID to a structure first and then reference it later with Json::redirect().
 * This allows for infinite loops in the event a structure can repeat itself within itself.
 *
 * @package Celestriode\JsonConstructure\Structures\Types
 */
class JsonRedirect extends AbstractJsonStructure
{
    /**
     * @var UuidInterface The UUID of the target structure that should be redirected to.
     */
    private $target;

    /**
     * @param UuidInterface $target The UUID of the target structure that should be redirected to.
     */
    public function __construct(UuidInterface $target)
    {
        $this->setTarget($target);
    }

    /**
     * Sets the redirect target.
     *
     * @param UuidInterface $target
     * @return JsonRedirect
     */
    public function setTarget(UuidInterface $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Returns the redirect target.
     *
     * @return UuidInterface
     */
    public function getTarget(): UuidInterface
    {
        return $this->target;
    }

    /**
     * @inheritDoc
     * @throws InvalidUUID
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        $parent = parent::compare($constructure, $other);

        $redirect = self::getFromUUID($this->getTarget())->compare($constructure, $other);

        return $parent && $redirect;
    }

    /**
     * @inheritDoc
     */
    protected function useGlobalAudits(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     * @throws InvalidUUID
     */
    public function getTypeName(): string
    {
        return self::getFromUUID($this->getTarget())->getTypeName();
    }

    /**
     * @inheritDoc
     * @throws InvalidUUID
     */
    public function equals(AbstractJsonStructure $other): bool
    {
        return self::getFromUUID($this->getTarget())->equals($other);
    }

    /**
     * @inheritDoc
     * @throws InvalidUUID
     */
    public function typesMatch(AbstractJsonStructure $other): bool
    {
        return self::getFromUUID($this->getTarget())->typesMatch($other);
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        // If there's a prettifer, return its result instead.

        if ($prettifier !== null) {

            return $prettifier->prettify($this);
        }

        // Otherwise reference the target.

        return '"' . uniqid("REDIRECT_") . '": "' . $this->getTarget()->toString() . '"';
    }
}