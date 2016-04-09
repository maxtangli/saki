<?php
namespace Saki\Game;

/**
 * A game player holding his own score, selfWind and tileArea.
 * @package Saki\Game
 */
class Player {
    // immutable
    private $no;

    // change via setter
    private $score; // todo move into Area
    /** @var Area */
    private $area;

    function __construct(int $no, int $score) {
        $this->no = $no;
        $this->score = $score;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('player[%s] wind[%s] score[%s]', $this->getNo(), $this->getTileArea()->getPlayerWind(), $this->getScore());
    }

    /**
     * @return int
     */
    function getNo() {
        return $this->no;
    }

    /**
     * @return int
     */
    function getScore() {
        return $this->score;
    }

    /**
     * @param $score
     */
    function setScore(int $score) {
        $this->score = $score;
    }

    /**
     * @return Area
     */
    function getTileArea() {
        if ($this->area === null) {
            throw new \BadMethodCallException('Bad method call on Area-uninitialized Player.');
        }
        return $this->area;
    }

    /**
     * @param Area $area
     */
    function setTileArea(Area $area) {
        $this->area = $area;
    }
}

