<?php
namespace Saki\Game;

use Saki\Util\Immutable;

/**
 * A context which defines how PrevailingStatus rolls.
 * Design note: may be implemented as 1 interface and 4 classes: E-4p, E-3p, ES-4p, ES-3p,
 * though here gives a simpler 1-class-implementation.
 * @package Saki\Game
 */
class PrevailingContext implements Immutable {
    private $playerCount;
    private $prevailingType;

    /**
     * @param int $playerCount
     * @param PrevailingType $prevailingType
     */
    function __construct(int $playerCount, PrevailingType $prevailingType) {
        $this->playerCount = $playerCount;
        $this->prevailingType = $prevailingType;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s%s', $this->getPlayerCount(), $this->getPrevailingType());
    }

    /**
     * @return int
     */
    function getPlayerCount() {
        return $this->playerCount;
    }

    /**
     * @return PrevailingType
     */
    function getPrevailingType() {
        return $this->prevailingType;
    }

    //region interfaces
    function valid(PrevailingStatus $status) {
        return $status->isBeforeOrSame($this->getSuddenDeathLast()) &&
        $status->getPrevailingWindTurn() <= $this->getPlayerCount();
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isCurrentPrevailingWindLast(PrevailingStatus $status) {
        $this->assertValid($status);
        return $status->getPrevailingWindTurn() == $this->getPlayerCount();
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isNormal(PrevailingStatus $status) {
        $this->assertValid($status);
        return $this->isNormalNotLast($status) || $this->isNormalLast($status);
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isNormalNotLast(PrevailingStatus $status) {
        $this->assertValid($status);
        return $status->isBefore($this->getNormalLast());
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isNormalLast(PrevailingStatus $status) {
        $this->assertValid($status);
        return $status->isSame($this->getNormalLast());
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isSuddenDeath(PrevailingStatus $status) {
        $this->assertValid($status);
        return $this->isSuddenDeathNotLast($status) || $this->isSuddenDeathLast($status);
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isSuddenDeathNotLast(PrevailingStatus $status) {
        $this->assertValid($status);
        return !$this->isNormal($status) && $status->isBefore($this->getSuddenDeathLast());
    }

    /**
     * @param PrevailingStatus $status
     * @return bool
     */
    function isSuddenDeathLast(PrevailingStatus $status) {
        $this->assertValid($status);
        return $status->isSame($this->getSuddenDeathLast());
    }

    //endregion

    //region impl
    protected function assertValid(PrevailingStatus $status) {
        if (!$this->valid($status)) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @return PrevailingStatus
     */
    protected function getNormalLast() {
        return new PrevailingStatus($this->getPrevailingType()->getNormalLast(), $this->getPlayerCount());
    }

    /**
     * @return PrevailingStatus
     */
    protected function getSuddenDeathLast() {
        return new PrevailingStatus($this->getPrevailingType()->getSuddenDeathLast(), $this->getPlayerCount());
    }
    //endregion
}