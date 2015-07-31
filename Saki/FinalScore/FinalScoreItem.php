<?php
namespace Saki\FinalScore;

class FinalScoreItem {
    private $rank; // 1-4
    private $finalScore;
    private $finalPoint;

    function __construct($rank, $finalScore, $finalPoint) {
        $this->rank = $rank;
        $this->finalScore = $finalScore;
        $this->finalPoint = $finalPoint;
    }

    function getRank() {
        return $this->rank;
    }

    function getFinalScore() {
        return $this->finalScore;
    }

    function getFinalPoint() {
        return $this->finalPoint;
    }
}