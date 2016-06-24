<?php
namespace Saki\Game;

/**
 * A PrevailingStatus whose rolling is defined by a PrevailingContext.
 * @package Saki\Game
 */
class PrevailingCurrent {
    /**
     * @param PrevailingContext $context
     * @return PrevailingCurrent
     */
    static function createFirst(PrevailingContext $context) {
        return new self($context, PrevailingStatus::createFirst(), 0);
    }

    // immutable
    private $context;
    // game variable
    private $status;
    private $seatWindTurn;

    /**
     * @param PrevailingContext $context
     * @param PrevailingStatus $status
     * @param int $seatWindTurn
     */
    function __construct(PrevailingContext $context, PrevailingStatus $status, int $seatWindTurn) {
        if (!$context->valid($status)) {
            throw new \InvalidArgumentException();
        }

        // immutable
        $this->context = $context;
        // game variable
        $this->status = $status;
        $this->seatWindTurn = $seatWindTurn;
    }

    /**
     * @param bool $keepDealer
     * @return $this|PrevailingCurrent
     */
    function toRolled(bool $keepDealer) {
        if ($keepDealer) {
            $nextSeatWindTurn = $this->getSeatWindTurn() + 1;
            return new PrevailingCurrent($this->getContext(), $this->getStatus(), $nextSeatWindTurn);
        }

        // not keep dealer + sudden death last => error
        if ($this->isSuddenDeathLast()) {
            throw new \BadMethodCallException();
        }

        // not keep dealer + not sudden death last => roll
        $nextStatus = $this->isCurrentPrevailingWindLast() ?
            $this->getStatus()->toNextPrevailingWind() :
            $this->getStatus()->toNextPrevailingWindTurn();
        return new PrevailingCurrent($this->getContext(), $nextStatus, 0);
    }

    /**
     * @param PrevailingStatus $status
     * @return PrevailingCurrent
     */
    function toDebugInitialized(PrevailingStatus $status) {
        return new PrevailingCurrent($this->getContext(), $status, 0);
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

    /**
     * @return int
     */
    function getSeatWindTurn() {
        return $this->seatWindTurn;
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