<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class Player {
    // immutable
    private $no;

    // change via setter
    private $score;

    // reset for each round
    private $selfWind;
    private $tileArea;

    function __construct($no, $score, Tile $selfWind) {
        $this->no = $no;
        $this->score = $score;
        $this->selfWind = $selfWind;
        $this->tileArea = new TileArea();
    }

    function reset(Tile $selfWind) {
        $this->selfWind = $selfWind;
        $this->tileArea->reset();
    }

    function __toString() {
        return sprintf('player[%s] wind[%s] score[%s]', $this->getNo(), $this->getSelfWind(), $this->getScore());
    }

    function getNo() {
        return $this->no;
    }

    function getScore() {
        return $this->score;
    }

    function setScore($score) {
        $this->score = $score;
    }

    function getSelfWind() {
        return $this->selfWind;
    }

    function setSelfWind(Tile $selfWind) {
        $this->selfWind = $selfWind;
    }

    function isSelfWind(Tile $tile) {
        return $tile == $this->getSelfWind();
    }

    function isDealer() {
        return $this->getSelfWind() == Tile::fromString('E');
    }

    function isLeisureFamily() {
        return !$this->isDealer();
    }

    function getTileArea() {
        return $this->tileArea;
    }
}