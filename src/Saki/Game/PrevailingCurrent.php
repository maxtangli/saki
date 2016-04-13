<?php
namespace Saki\Game;

/**
 * A PrevailingStatus whose rolling is defined by a PrevailingContext.
 * @package Saki\Game
 */
class PrevailingCurrent {
    // immutable
    private $context;
    // game variable
    private $status;

    /**
     * @param PrevailingContext $context
     * @param PrevailingStatus|null $status
     */
    function __construct(PrevailingContext $context, PrevailingStatus $status = null) {
        $actualStatus = $status ?? PrevailingStatus::createFirst();
        if (!$context->valid($actualStatus)) {
            throw new \InvalidArgumentException();
        }

        // immutable
        $this->context = $context;
        // game variable
        $this->status = $actualStatus;
    }

    /**
     * @param bool $keepDealer
     * @return $this|PrevailingCurrent
     */
    function toRolled(bool $keepDealer) {
        if ($keepDealer) {
            return $this;
        }

        // not keep dealer + sudden death last => error
        if ($this->isSuddenDeathLast()) {
            throw new \BadMethodCallException();
        }

        // not keep dealer + not sudden death last => roll
        $nextStatus = $this->isCurrentPrevailingWindLast() ?
            $this->getStatus()->toNextPrevailingWind() :
            $this->getStatus()->toNextPrevailingWindTurn();
        return new PrevailingCurrent($this->getContext(), $nextStatus);
    }

    /**
     * @param PrevailingStatus $status
     * @return PrevailingCurrent
     */
    function toDebugInited(PrevailingStatus $status) {
        return new PrevailingCurrent($this->getContext(), $status);
    }

    /**
     * @return PrevailingContext
     */
    function getContext() {
        return $this->context;
    }

    /**
     * @return PrevailingStatus
     */
    function getStatus() {
        return $this->status;
    }

    //region delegates of PrevailingContext
    /**
     * normal          | suddenDeath
     * not-last | last | not-last | last
     */
    /**
     * @return bool
     */
    function isCurrentPrevailingWindLast() {
        return $this->getContext()->isCurrentPrevailingWindLast($this->getStatus());
    }

    /**
     * @return bool
     */
    function isNormal() {
        return $this->getContext()->isNormal($this->getStatus());
    }

    /**
     * @return bool
     */
    function isNormalNotLast() {
        return $this->getContext()->isNormalNotLast($this->getStatus());
    }

    /**
     * @return bool
     */
    function isNormalLast() {
        return $this->getContext()->isNormalLast($this->getStatus());
    }

    /**
     * @return bool
     */
    function isSuddenDeath() {
        return $this->getContext()->isSuddenDeath($this->getStatus());
    }

    /**
     * @return bool
     */
    function isSuddenDeathNotLast() {
        return $this->getContext()->isSuddenDeathNotLast($this->getStatus());
    }

    /**
     * @return bool
     */
    function isSuddenDeathLast() {
        return $this->getContext()->isSuddenDeathLast($this->getStatus());
    }
    //endregion
}