<?php
namespace Saki\Win\Fu;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\Tile;
use Saki\Util\Singleton;
use Saki\Win\Waiting\WaitingType;
use Saki\Win\Yaku\Fan1\PinfuYaku;
use Saki\Win\Yaku\Fan2\SevenPairsYaku;
use Saki\Win\Yaku\YakuItemList;

/**
 * @see http://ja.wikipedia.org/wiki/麻雀の得点計算#.E7.AC.A6.E3.81.AE.E8.A8.88.E7.AE.97
 * @package Saki\Win\Fu
 */
class FuAnalyzer extends Singleton {
    /**
     * @param FuTarget $target
     * @return FuResult
     */
    function getResult(FuTarget $target) {
        $specialYakuTotalFu = $this->getSpecialYakuTotalFu($target->getYakuList(), $target->isSelfPhase());
        if ($specialYakuTotalFu !== false) {
            return new FuResult($specialYakuTotalFu, 0, 0, [], 0, 0, 0, 0, $specialYakuTotalFu, $specialYakuTotalFu);
        } else {
            $baseFu = $this->getBaseFu();
            $winSetFu = $this->getWinSetListFu($target->getAllMeldList());
            $winSetFuResults = $this->getWinSetListFuResult($target->getAllMeldList());
            $pairFu = $this->getPairFu($target->getPairMeld(), $target->getSeatWindTile(), $target->getPrevailingWindTile());
            $waitingTypeFu = $this->getWaitingTypeFu($target->getWaitingType());
            $concealedFu = $this->getConcealedFu($target->isConcealed());
            $tsumoFu = $this->getTsumoFu($target->isSelfPhase());
            $roughTotalFu = $baseFu + $winSetFu + $pairFu + $waitingTypeFu + $concealedFu + $tsumoFu;
            $totalFu = $this->roughToTotal($roughTotalFu);
            return new FuResult($specialYakuTotalFu, $baseFu, $winSetFu, $winSetFuResults, $pairFu, $waitingTypeFu, $concealedFu, $tsumoFu, $roughTotalFu, $totalFu);
        }
    }

    /**
     * @param YakuItemList $yakuList
     * @param $tsumo
     * @return bool|int
     */
    function getSpecialYakuTotalFu(YakuItemList $yakuList, $tsumo) {
        if ($yakuList->getTotalFan() > 4) {
            return 0;
        }

        // 平和
        if ($yakuList->valueExist(PinfuYaku::create())) {
            if ($tsumo) { // ツモ平和	一律20符
                return 20;
            } else { // 喰い平和	一律30符
                return 30;
            }
        }

        // 七対子	一律25符
        if ($yakuList->valueExist(SevenPairsYaku::create())) {
            return 25;
        }

        return false;
    }

    /**
     * @param MeldList $meldList
     * @return int
     */
    function getWinSetListFu(MeldList $meldList) {
        return $meldList->getSum([$this, 'getWinSetFu']);
    }

    /**
     * @param MeldList $meldList
     * @return WinSetFuResult[]
     */
    function getWinSetListFuResult(MeldList $meldList) {
        $aggregator = function (array $result, Meld $meld) {
            $winSetFu = $this->getWinSetFu($meld);
            if ($winSetFu > 0) {
                $result[] = new WinSetFuResult($meld, $winSetFu);
            }
            return $result;
        };
        return array_reduce($meldList->toArray(), $aggregator, []);
    }

    /**
     * @param Meld $winSetMeld
     * @return int
     */
    function getWinSetFu(Meld $winSetMeld) {
        /**
         * 面子
         * - 順子  0符
         * - 刻子
         *        中張 么九
         * - 明刻  2符  4符
         * - 暗刻  4符  8符
         * - 明槓  8符 16符
         * - 暗槓 16符 32符
         */
        if ($winSetMeld->isPungOrKong()) {
            $baseFu = 2;
            $termRatio = $winSetMeld[0]->isTermOrHonour() ? 2 : 1;
            $concealedRatio = $winSetMeld->isConcealed() ? 2 : 1;
            $kongRatio = $winSetMeld->isKong() ? 4 : 1;
            $meldFu = $baseFu * $termRatio * $concealedRatio * $kongRatio;
            return $meldFu;
        } else {
            return 0;
        }
    }

    /**
     * @param Meld $pairMeld
     * @param Tile $seatWind
     * @param Tile $prevailingWind
     * @return int
     */
    function getPairFu(Meld $pairMeld, Tile $seatWind, Tile $prevailingWind) {
        if (!$pairMeld->isPair()) {
            throw new \InvalidArgumentException();
        }

        /**
         * 雀頭
         * - 数牌   0符
         * - 客風   0符
         * - 自風   2符
         * - 場風   2符
         * - 三元牌 2符
         * - 連風牌 4符
         */
        $pairTile = $pairMeld[0];
        $dragonFu = $pairTile->isDragon() ? 2 : 0;
        $seatWindFu = $pairTile == $seatWind ? 2 : 0;
        $prevailingWindFu = $pairTile == $prevailingWind ? 2 : 0;
        $pairFu = $dragonFu + $seatWindFu + $prevailingWindFu;
        return $pairFu;
    }

    /**
     * @param WaitingType $waitingType
     * @return int
     */
    function getWaitingTypeFu(WaitingType $waitingType) {
        /**
         * 待ち
         * - 両面待ち 0符
         * - 双碰待ち 0符
         * - 嵌張待ち 2符
         * - 辺張待ち 2符
         * - 単騎待ち 2符
         */
        $targetWaitingTypes = [
            WaitingType::create(WaitingType::MIDDLE_CHOW_WAITING),
            WaitingType::create(WaitingType::ONE_SIDE_CHOW_WAITING),
            WaitingType::create(WaitingType::PAIR_WAITING),
        ];
        $waitingTypeFu = in_array($waitingType, $targetWaitingTypes) ? 2 : 0;
        return $waitingTypeFu;
    }

    /**
     * @return int
     */
    function getBaseFu() {
        return 20; // 副底	20符
    }

    /**
     * @param bool $isConcealed
     * @return int
     */
    function getConcealedFu(bool $isConcealed) {
        return $isConcealed ? 10 : 0; // 門前加符	10符
    }

    /**
     * @param bool $isTsumo
     * @return int
     */
    function getTsumoFu(bool $isTsumo) {
        return $isTsumo ? 2 : 0; // ツモ符	2符
    }

    /**
     * @param int $roughFu
     * @return int
     */
    function roughToTotal(int $roughFu) {
        return intval(ceil($roughFu / 10) * 10); // 各項目をすべて加算し、その合計を10符単位に切り上げたものである
    }
}