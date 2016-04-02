<?php
namespace Saki\Win\Fu;

class FuCountResult {
    private $specialYakuTotalFuCount;
    private $baseFuCount;
    private $winSetFuCount;
    private $winSetFuCountResults;
    private $pairFuCount;
    private $waitingTypeFuCount;
    private $concealedFuCount;
    private $winBySelfFuCount;
    private $roughTotalFuCount;
    private $totalFuCount;

    function __construct($specialYakuTotalFuCount, $baseFuCount, $winSetFuCount, $winSetFuCountResults, $pairFuCount, $waitingTypeFuCount, $concealedFuCount, $winBySelfFuCount, $roughTotalFuCount, $totalFuCount) {
        $this->specialYakuTotalFuCount = $specialYakuTotalFuCount;
        $this->baseFuCount = $baseFuCount;
        $this->winSetFuCount = $winSetFuCount;
        $this->winSetFuCountResults = $winSetFuCountResults;
        $this->pairFuCount = $pairFuCount;
        $this->waitingTypeFuCount = $waitingTypeFuCount;
        $this->concealedFuCount = $concealedFuCount;
        $this->winBySelfFuCount = $winBySelfFuCount;
        $this->roughTotalFuCount = $roughTotalFuCount;
        $this->totalFuCount = $totalFuCount;
    }

    function getSpecialYakuTotalFuCount() {
        return $this->specialYakuTotalFuCount;
    }

    function getBaseFuCount() {
        return $this->baseFuCount;
    }

    function getWinSetFuCount() {
        return $this->winSetFuCount;
    }

    /**
     * @return WinSetFuCountResult[]
     */
    function getWinSetFuCountResults() {
        return $this->winSetFuCountResults;
    }

    function getPairFuCount() {
        return $this->pairFuCount;
    }

    function getWaitingTypeFuCount() {
        return $this->waitingTypeFuCount;
    }

    function getConcealedFuCount() {
        return $this->concealedFuCount;
    }

    function getWinBySelfFuCount() {
        return $this->winBySelfFuCount;
    }

    function getRoughTotalFuCount() {
        return $this->roughTotalFuCount;
    }

    function getTotalFuCount() {
        return $this->totalFuCount;
    }
}