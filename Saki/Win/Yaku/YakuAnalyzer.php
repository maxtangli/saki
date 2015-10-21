<?php

namespace Saki\Win\Yaku;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
use Saki\Win\Yaku\Fan1\GreenValueTilesYaku;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Fan1\RedValueTilesYaku;
use Saki\Win\Yaku\Fan1\RoundWindValueTilesYaku;
use Saki\Win\Yaku\Fan1\SelfWindValueTilesYaku;
use Saki\Win\Yaku\Fan1\WhiteValueTilesYaku;
use Saki\Win\Yaku\Yakuman2\FourConcealedTriplesOnePairWaitingYaku;
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;

class YakuAnalyzer {
    private $yakus;

    static function getDefaultYakus() {
        return [
            // 1 fan
            ReachYaku::getInstance(),
            RedValueTilesYaku::getInstance(),
            WhiteValueTilesYaku::getInstance(),
            GreenValueTilesYaku::getInstance(),
            SelfWindValueTilesYaku::getInstance(),
            RoundWindValueTilesYaku::getInstance(),
            AllSimplesYaku::getInstance(),
            AllRunsYaku::getInstance(),
            // yaku man
            FourConcealedTriplesYaku::getInstance(),
            // w yaku man
            FourConcealedTriplesOnePairWaitingYaku::getInstance(),
        ];
    }

    function __construct(array $yakus = null) {
        $this->yakus = $yakus !== null ? $yakus : static::getDefaultYakus();
    }

    /**
     * @return Yaku[]
     */
    function getYakus() {
        return $this->yakus;
    }

    function analyzeYakuList(WinSubTarget $subTarget) {
        $yakuList = new YakuList([], $subTarget->isExposed());
        foreach ($this->getYakus() as $yaku) {
            if ($yaku->existIn($subTarget)) {
                $yakuList->push($yaku);
            }
        }
        $yakuList->normalize(); // remove mutually-excluded yaku
        return $yakuList;
    }
}