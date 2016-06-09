<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ComparableSequence;
use Saki\Util\Enum;
use Saki\Util\Immutable;

/**
 * Inner class for OpenRecord.
 * @package Saki\Game
 */
class OpenRecordType extends Enum {
    const DISCARD = 0;
    const EXTEND_KONG = 1;
    const DECLARED = 2;
}

/**
 * A record for a open operation includes discard and extendKong.
 * Note that open happens only in private phase, thus Turn.SeatWind means open actor.
 * @package Saki\Game
 */
class OpenRecord implements Immutable {
    use ComparableSequence;

    function compareTo($other) {
        /** @var OpenRecord $other */
        $other = $other;
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

    /**
     * @param Turn $turn
     * @param Tile $tile
     * @param bool $isDiscard
     */
    function __construct(Turn $turn, Tile $tile, bool $isDiscard) {
        $this->turn = $turn;
        $this->tile = $tile;
        $this->openType = OpenRecordType::create($isDiscard ? OpenRecordType::DISCARD : OpenRecordType::EXTEND_KONG);
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
        $record->openType = OpenRecordType::create(OpenRecordType::DECLARED);
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
        return $this->openType->getValue() == OpenRecordType::DISCARD;
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