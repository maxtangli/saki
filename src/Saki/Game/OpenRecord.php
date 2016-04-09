<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ComparableTimeLine;
use Saki\Util\Immutable;

/**
 * A record for a open operation, where open operation includes discard and plusKong.
 * Note that open happens in private phase only, thus RoundTurn.PlayerWind means open actor.
 * @package Saki\Game
 */
class OpenRecord implements Immutable {
    use ComparableTimeLine;

    function compareTo($other) {
        /** @var OpenRecord $other */
        $other = $other;
        return $this->getRoundTurn()->compareTo($other->getRoundTurn());
    }

    private $roundTurn;
    private $tile;
    private $isDiscard;

    /**
     * @param RoundTurn $roundTurn
     * @param Tile $tile
     * @param bool $isDiscard
     */
    function __construct(RoundTurn $roundTurn, Tile $tile, bool $isDiscard) {
        $this->roundTurn = $roundTurn;
        $this->tile = $tile;
        $this->isDiscard = $isDiscard;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s,%s,%s',
            $this->getRoundTurn(), $this->getActor(), $this->getTile(), $this->isDiscard());
    }

    /**
     * @return RoundTurn
     */
    function getRoundTurn() {
        return $this->roundTurn;
    }

    /**
     * @return PlayerWind
     */
    function getActor() {
        return $this->getRoundTurn()->getPlayerWind();
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
        $this->isLaterThanOrSame($recordList->getLast());
    }
}