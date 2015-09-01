<?php
namespace Saki\Win\Fu;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Util\Singleton;
use Saki\Win\WaitingType;
use Saki\Win\Yaku\AllRunsYaku;
use Saki\Win\Yaku\SevenPairsYaku;
use Saki\Win\Yaku\YakuList;

/**
 * ref: http://ja.wikipedia.org/wiki/%E9%BA%BB%E9%9B%80%E3%81%AE%E5%BE%97%E7%82%B9%E8%A8%88%E7%AE%97#.E7.AC.A6.E3.81.AE.E8.A8.88.E7.AE.97
 * @package Saki\Win
 */
class FuCountAnalyzer extends Singleton {

    function getResult(FuCountTarget $target) {
        $specialYakuTotalFuCount = $this->getSpecialYakuTotalFuCount($target->getYakuList(), $target->isSelfPhase());
        if ($specialYakuTotalFuCount !== false) {
            return new FuCountResult($specialYakuTotalFuCount, 0, 0, [], 0, 0, 0, 0, $specialYakuTotalFuCount, $specialYakuTotalFuCount);
        } else {
            $baseFuCount = $this->getBaseFuCount();
            $winSetFuCount = $this->getWinSetListFuCount($target->getAllMeldList());
            $winSetFuCountResults = $this->getWinSetListFuCountResult($target->getAllMeldList());
            $pairFuCount = $this->getPairFuCount($target->getPairMeld(), $target->getSelfWind(), $target->getRoundWind());
            $waitingTypeFuCount = $this->getWaitingTypeFuCount($target->getWaitingType());
            $concealedFuCount = $this->getConcealedFuCount($target->isConcealed());
            $winBySelfFuCount = $this->getWinBySelfFuCount($target->isSelfPhase());
            $roughTotalFuCount = $baseFuCount + $winSetFuCount + $pairFuCount + $waitingTypeFuCount + $concealedFuCount + $winBySelfFuCount;
            $totalFuCount = $this->roughToTotal($roughTotalFuCount);
            return new FuCountResult($specialYakuTotalFuCount, $baseFuCount, $winSetFuCount, $winSetFuCountResults, $pairFuCount, $waitingTypeFuCount, $concealedFuCount, $winBySelfFuCount, $roughTotalFuCount, $totalFuCount);
        }
    }

    function getSpecialYakuTotalFuCount(YakuList $yakuList, $winBySelf) {
        if ($yakuList->getFanCount() > 4) {
            return 0;
        }

        // 平和
        if ($yakuList->valueExist(AllRunsYaku::getInstance())) {
            if ($winBySelf) { // ツモ平和	一律20符
                return 20;
            } else { // 喰い平和	一律30符
                return 30;
            }
        }

        // 七対子	一律25符
        if ($yakuList->valueExist(SevenPairsYaku::getInstance())) {
            return 25;
        }

        return false;
    }

    function getWinSetListFuCount(MeldList $meldList) {
        return array_reduce($meldList->toArray(), function ($totalFuCount, Meld $meld) {
            return $totalFuCount + $this->getWinSetFuCount($meld);
        }, 0);
    }

    function getWinSetListFuCountResult(MeldList $meldList) {
        return array_reduce($meldList->toArray(), function (array $result, Meld $meld) {
            $winSetFuCount = $this->getWinSetFuCount($meld);
            if ($winSetFuCount > 0) {
                $result[] = new MeldFuCountResult($meld, $winSetFuCount);
            }
            return $result;
        }, []);
    }

    function getWinSetFuCount(Meld $winSetMeld) {
        /**
         * 面子
         * 順子  0符
         * 刻子 中張 么九
         * 明刻  2符  4符
         * 暗刻  4符  8符
         * 明槓  8符 16符
         * 暗槓 16符 32符
         */
        if ($winSetMeld->isTripleOrQuad()) {
            $baseFu = 2;
            $terminalRatio = $winSetMeld[0]->isTerminalOrHonor() ? 2 : 1;
            $concealedRatio = $winSetMeld->isConcealed() ? 2 : 1;
            $quadRatio = $winSetMeld->isQuad() ? 4 : 1;
            $meldFuCount = $baseFu * $terminalRatio * $concealedRatio * $quadRatio;
            return $meldFuCount;
        } else {
            return 0;
        }
    }

    function getPairFuCount(Meld $pairMeld, Tile $selfWind, Tile $roundWind) {
        if (!$pairMeld->isPair()) {
            throw new \InvalidArgumentException();
        }

        /**
         * 雀頭
         * 数牌   0符
         * 客風   0符
         * 自風   2符
         * 場風   2符
         * 三元牌 2符
         * 連風牌 4符
         */
        $pairTile = $pairMeld[0];
        $dragonFuCount = $pairTile->isDragon() ? 2 : 0;
        $selfWindFuCount = $pairTile == $selfWind ? 2 : 0;
        $roundWindFuCount = $pairTile == $roundWind ? 2 : 0;
        $pairFuCount = $dragonFuCount + $selfWindFuCount + $roundWindFuCount;
        return $pairFuCount;
    }

    function getWaitingTypeFuCount(WaitingType $waitingType) {
        /**
         * 待ち
         * 両面待ち 0符
         * 双碰待ち 0符
         * 嵌張待ち 2符
         * 辺張待ち 2符
         * 単騎待ち 2符
         */
        $targetWaitingTypes = [
            WaitingType::getInstance(WaitingType::MIDDLE_RUN_WAITING),
            WaitingType::getInstance(WaitingType::ONE_SIDE_RUN_WAITING),
            WaitingType::getInstance(WaitingType::PAIR_WAITING),
        ];
        $waitingTypeFuCount = in_array($waitingType, $targetWaitingTypes) ? 2 : 0;
        return $waitingTypeFuCount;
    }

    function getBaseFuCount() {
        return 20; // 副底	20符
    }

    function getConcealedFuCount($isConcealed) {
        return $isConcealed ? 10 : 0; // 門前加符	10符
    }

    function getWinBySelfFuCount($isWinBySelf) {
        return $isWinBySelf ? 2 : 0; // ツモ符	2符
    }

    function roughToTotal($roughFuCount) {
        return intval(ceil($roughFuCount / 10) * 10); // 各項目をすべて加算し、その合計を10符単位に切り上げたものである
    }

    /**
     * @return FuCountAnalyzer
     */
    static function getInstance() {
        return parent::getInstance();
    }
}