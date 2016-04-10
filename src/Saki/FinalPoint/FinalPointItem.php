<?php
namespace Saki\FinalPoint;

class FinalPointItem {
    private $rank; // 1-4
    private $finalPoint;
    private $finalPointNumber;

    function __construct($rank, $finalPoint, $finalPointNumber) {
        $this->rank = $rank;
        $this->finalPoint = $finalPoint;
        $this->finalPointNumber = $finalPointNumber;
    }

    function getRank() {
        return $this->rank;
    }

    function getFinalPoint() {
        return $this->finalPoint;
    }

    function getFinalPointNumber() {
        return $this->finalPointNumber;
    }
}