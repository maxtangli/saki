<?php
namespace Saki\Game;

/**
 * A PrevailingStatus whose rolling is defined by a PrevailingContext.
 * @package Saki\Game
 */
class Prevailing {
    /**
     * @param PrevailingContext $context
     * @return Prevailing
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
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s continue', $this->getStatus(), $this->getSeatWindTurn());
    }

    /**
     * @param bool $keepDealer
     * @return $this|Prevailing
     */
    function toRolled(bool $keepDealer) {
        if ($keepDealer) {
            $nextSeatWindTurn = $this->getSeatWindTurn() + 1;
            return new Prevailing($this->getContext(), $this->getStatus(), $nextSeatWindTurn);
        }

        // not keep dealer + sudden death last => error
        if ($this->isSuddenDeathLast()) {
            throw new \BadMethodCallException();
        }

        // not keep dealer + not sudden death last => roll
        $nextStatus = $this->isCurrentPrevailingWindLast() ?
            $this->getStatus()->toNextPrevailingWind() :
            $this->getStatus()->toNextPrevailingWindTurn();
        return new Prevailing($this->getContext(), $nextStatus, 0);
    }

    /**
     * @param PrevailingStatus $status
     * @return Prevailing
     */
    function toDebugInitialized(PrevailingStatus $status) {
        return new Prevailing($this->getContext(), $status, 0);
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

    /**
     * Sugar method.
     * @return Tile\Tile
     */
    function getWindTile() {
        return $this->getStatus()->getPrevailingWind()->getWindTile();
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

    /**
     * @return bool
     */
    function isNormalLastOrSuddenDeath() {
        return $this->getContext()->isNormalLastOrSuddenDeath($this->getStatus());
    }
    //endregion
}