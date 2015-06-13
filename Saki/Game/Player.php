<?php
namespace Saki\Game;

use Saki\Util\Utils;

class Player {
    private $no;
    private $score;

    function __construct($no, $score) {
        $this->no = $no;
        $this->score = $score;
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
}