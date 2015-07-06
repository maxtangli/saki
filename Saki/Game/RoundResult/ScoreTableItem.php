<?php
namespace Saki\Game\RoundResult;

class ScoreTableItem {
    private $receiverIsDealer;
    private $baseScore;

    function __construct($receiverIsDealer, $baseScore) {
        $this->receiverIsDealer = $receiverIsDealer;
        $this->baseScore = $baseScore;;
    }

    function getReceiverIsDealer() {
        return $this->receiverIsDealer;
    }

    /**
     * @return int
     */
    function getBaseScore() {
        return $this->baseScore;
    }

    /**
     * @param bool $winBySelf
     * @param bool $payerIsDealer
     * @return int
     */
    function getPayScore($winBySelf, $payerIsDealer) {
        if ($this->getReceiverIsDealer() && $payerIsDealer) {
            throw new \InvalidArgumentException();
        }

        /**
         * https://ja.wikipedia.org/wiki/%E9%BA%BB%E9%9B%80%E3%81%AE%E5%BE%97%E7%82%B9%E8%A8%88%E7%AE%97
         * 各自の負担額の計算式
         * 基本点「子のツモ和了が発生した時に、他の子が支払う点数」
         * 子のロン和了の点数            ＝    基本点 x4
         * 親のロン和了の点数            ＝    基本点 x6
         * 子のツモ和了の時の子の払い    ＝    基本点 x1 (総計 x4)
         * 子のツモ和了の時の親の払い    ＝    基本点 x2 (総計 x4)
         * 親のツモ和了の時の子の払い    ＝    基本点 x2 (総計 x6)
         */
        if ($winBySelf) {
            $ratio = ($this->getReceiverIsDealer() || $payerIsDealer) ? 2 : 1;
        } else {
            $ratio = $this->getReceiverIsDealer() ? 6 : 4;
        }
        $score = $this->getBaseScore() * $ratio;

        $score = intval(ceil($score / 100) * 100); // 切り上げ. e.x.640->700
        return $score;
    }
}