<?php
namespace Saki\Win\Fu;

class FuResult {
    private $specialYakuTotalFu;
    private $baseFu;
    private $winSetFu;
    private $winSetFuResults;
    private $pairFu;
    private $waitingTypeFu;
    private $concealedFu;
    private $tsumoFu;
    private $roughTotalFu;
    private $totalFu;

    function __construct($specialYakuTotalFu, $baseFu, $winSetFu, $winSetFuResults, $pairFu, $waitingTypeFu, $concealedFu, $tsumoFu, $roughTotalFu, $totalFu) {
        $this->specialYakuTotalFu = $specialYakuTotalFu;
        $this->baseFu = $baseFu;
        $this->winSetFu = $winSetFu;
        $this->winSetFuResults = $winSetFuResults;
        $this->pairFu = $pairFu;
        $this->waitingTypeFu = $waitingTypeFu;
        $this->concealedFu = $concealedFu;
        $this->tsumoFu = $tsumoFu;
        $this->roughTotalFu = $roughTotalFu;
        $this->totalFu = $totalFu;
    }

    function getSpecialYakuTotalFu() {
        return $this->specialYakuTotalFu;
    }

    function getBaseFu() {
        return $this->baseFu;
    }

    function getWinSetFu() {
        return $this->winSetFu;
    }

    /**
     * @return MeldFuResult[]
     */
    function getWinSetFuResults() {
        return $this->winSetFuResults;
    }

    function getPairFu() {
        return $this->pairFu;
    }

    function getWaitingTypeFu() {
        return $this->waitingTypeFu;
    }

    function getConcealedFu() {
        return $this->concealedFu;
    }

    function getTsumoFu() {
        return $this->tsumoFu;
    }

    function getRoughTotalFu() {
        return $this->roughTotalFu;
    }

    function getTotalFu() {
        return $this->totalFu;
    }
}