<?php
namespace Saki\Game;

class GameResultItem {
    private $rank; // 1-4
    private $finalScore;
    private $scorePoint;

    function __construct($rank, $finalScore, $scorePoint) {
        $this->rank = $rank;
        $this->finalScore = $finalScore;
        $this->scorePoint = $scorePoint;
    }

    function getRank() {
        return $this->rank;
    }

    function getFinalScore() {
        return $this->finalScore;
    }

    function getScorePoint() {
        return $this->scorePoint;
    }
}