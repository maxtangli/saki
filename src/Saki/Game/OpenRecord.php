<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ComparableSequence;
use Saki\Util\Enum;
use Saki\Util\Immutable;

/**
 * Inner class for OpenRecord.
 * @package Saki\Game
 */
class OpenType extends Enum {
    const DISCARD = 0;
    const EXTEND_KONG = 2;
}

/**
 * A record for a open operation includes discard and extendKong.
 * Note that open happens only in private phase, thus Turn.SeatWind means open actor.
 * @package Saki\Game
 */
class OpenRecord implements Immutable {
    use ComparableSequence;

    /**
     * @param OpenRecord $other
     * @return bool
     */
    function compareTo($other) {
        return $this->getTurn()->compareTo($other->getTurn());
    }

    /**
     * @return \Closure
     */
    static function getToTileCallback() {
        return function (OpenRecord $record) {
            return $record->getTile();
        };
    }

    private $turn;
    private $tile;
    private $openType;
    private $isRiichi;
    private $isDeclared;

    /**
     * @param Turn $turn
     * @param Tile $tile
     * @param bool $isDiscard
     * @param bool $isRiichi
     */
    function __construct(Turn $turn, Tile $tile, bool $isDiscard, bool $isRiichi = false) {
        $validIsRiichi = !$isRiichi || $isDiscard;
        if (!$validIsRiichi) {
            throw new \InvalidArgumentException();
        }

        $this->turn = $turn;
        $this->tile = $tile;
        $this->openType = OpenType::create($isDiscard ? OpenType::DISCARD : OpenType::EXTEND_KONG);
        $this->isRiichi = $isRiichi;
        $this->isDeclared = false;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s,%s,%s',
            $this->getTurn(), $this->getActor(), $this->getTile(), $this->isDiscard());
    }

    /**
     * @return OpenRecord
     */
    function toDeclared() {
        if (!$this->isDiscard()) {
            throw new \BadMethodCallException();
        }
        $record = new self($this->getTurn(), $this->getTile(), true);
        $record->isDeclared = true;
        return $record;
    }

    /**
     * @return Target
     */
    function toTarget() {
        $targetType = TargetType::create($this->isDiscard() ? TargetType::DISCARD : TargetType::KONG);
        return new Target($this->getTile(), $targetType, $this->getActor());
    }

    /**
     * @return Turn
     */
    function getTurn() {
        return $this->turn;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->getTurn()->getSeatWind();
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->tile;
    }

    /**
     * @return boolean
     */
    function isDiscard() {
        return $this->openType->getValue() == OpenType::DISCARD;
    }

    /**
     * @return bool
     */
    function isRiichi() {
        return $this->isRiichi;
    }

    /**
     * @return bool
     */
    function isDeclared() {
        return $this->isDeclared;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isSelfDiscard(SeatWind $seatWind) {
        return $this->isDiscard() && $seatWind == $this->getActor();
    }

    /**
     * @param ArrayList $recordList
     * @return bool
     */
    function validNewOf(ArrayList $recordList) {
        return $recordList->isEmpty() ||
        $this->isAfterOrSame($recordList->getLast());
    }
}