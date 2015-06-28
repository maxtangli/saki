<?php
namespace Saki\Game;

use Saki\Tile;
use Saki\Util\Utils;

class Player {
    private $no;
    private $score;
    private $selfWind;

    function __construct($no, $score, Tile $selfWind) {
        $this->no = $no;
        $this->score = $score;
        $this->selfWind = $selfWind;
    }

    function __toString() {
        return 'p'.$this->getNo();
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
}