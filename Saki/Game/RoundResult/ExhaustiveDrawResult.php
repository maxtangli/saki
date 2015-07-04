<?php
namespace Saki\Game\RoundResult;

use Saki\Game\Player;
use Saki\Util\Utils;

class ExhaustiveDrawResult extends RoundResult {
    private $allPlayers;
    private $isWaitings;

    /**
     * @param Player[] $allPlayers
     * @param bool[] $isWaitings
     */
    function __construct(array $allPlayers, array $isWaitings) {
        $valid = count($allPlayers) == count($isWaitings);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->allPlayers = $allPlayers;
        $this->isWaitings = $isWaitings;
    }

    /**
     * @param Player $player
     * @return ScoreDelta
     */
    function getScoreDeltaInt(Player $player) {
        /**
         * https://ja.wikipedia.org/wiki/麻雀の点
         * 不聴罰符（ノーテンばっぷ）
         * - ノーテン罰符は常に計3000点である。
         * - 3000点をノーテンの者が等分して払い、その3000点を聴牌していた者が等分して受け取る。
         * - 和了点のような親か子での違いはない。
         */
        $waitingCount = $this->getIsWaitingCount();
        $notWaitingCount = $this->getNotWaitingCount();
        if ($waitingCount == 0 || $notWaitingCount == 0) {
            return 0;
        } else {
            $totalDelta = 3000;
            $delta = $this->isWaiting($player) ? $totalDelta / $waitingCount : -$totalDelta / $notWaitingCount;
            return $delta;
        }
    }

    function isWaiting(Player $player) {
        $i = array_search($player, $this->allPlayers, false);
        if ($i === false) {
            throw new \InvalidArgumentException();
        }
        return $this->isWaitings[$i];
    }

    function getIsWaitingCount() {
        return Utils::array_filter_count($this->isWaitings, function ($v) {
            return $v == true;
        });
    }

    function getNotWaitingCount() {
        return count($this->isWaitings) - $this->getIsWaitingCount();
    }
}