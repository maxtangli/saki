<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ComparableSequence;
use Saki\Util\Immutable;

/**
 * A record for a open operation, where open operation includes discard and plusKong.
 * Note that open happens in private phase only, thus Turn.SeatWind means open actor.
 * @package Saki\Game
 */
class OpenRecord implements Immutable {
    use ComparableSequence;

    function compareTo($other) {
        /** @var OpenRecord $other */
        $other = $other;
        return $this->getTurn()->compareTo($other->getTurn());
    }

    private $turn;
    private $tile;
    private $isDiscard;

    /**
     * @param Turn $turn
     * @param Tile $tile
     * @param bool $isDiscard
     */
    function __construct(Turn $turn, Tile $tile, bool $isDiscard) {
        $this->turn = $turn;
        $this->tile = $tile;
        $this->isDiscard = $isDiscard;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s,%s,%s',
            $this->getTurn(), $this->getActor(), $this->getTile(), $this->isDiscard());
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
        return $this->isDiscard;
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