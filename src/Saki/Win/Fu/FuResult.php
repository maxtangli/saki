<?php
namespace Saki\Win\Fu;

/**
 * @package Saki\Win\Fu
 */
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

    /**
     * @param int $specialYakuTotalFu
     * @param int $baseFu
     * @param int $winSetFu
     * @param WinSetFuResult[] $winSetFuResults
     * @param int $pairFu
     * @param int $waitingTypeFu
     * @param int $concealedFu
     * @param int $tsumoFu
     * @param int $roughTotalFu
     * @param int $totalFu
     */
    function __construct(int $specialYakuTotalFu,
                         int $baseFu,
                         int $winSetFu,
                         array $winSetFuResults,
                         int $pairFu,
                         int $waitingTypeFu,
                         int $concealedFu,
                         int $tsumoFu,
                         int $roughTotalFu,
                         int $totalFu) {
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

    /**
     * @return int
     */
    function getSpecialYakuTotalFu() {
        return $this->specialYakuTotalFu;
    }

    /**
     * @return int
     */
    function getBaseFu() {
        return $this->baseFu;
    }

    /**
     * @return int
     */
    function getWinSetFu() {
        return $this->winSetFu;
    }

    /**
     * @return array|WinSetFuResult[]
     */
    function getWinSetFuResults() {
        return $this->winSetFuResults;
    }

    /**
     * @return int
     */
    function getPairFu() {
        return $this->pairFu;
    }

    /**
     * @return int
     */
    function getWaitingTypeFu() {
        return $this->waitingTypeFu;
    }

    /**
     * @return int
     */
    function getConcealedFu() {
        return $this->concealedFu;
    }

    /**
     * @return int
     */
    function getTsumoFu() {
        return $this->tsumoFu;
    }

    /**
     * @return int
     */
    function getRoughTotalFu() {
        return $this->roughTotalFu;
    }

    /**
     * @return int
     */
    function getTotalFu() {
        return $this->totalFu;
    }
}