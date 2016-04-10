<?php
namespace Saki\RoundResult;

use Saki\Game\Player;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

class ExhaustiveDrawRoundResult extends RoundResult {
    private $players;
    private $isWaitings;

    /**
     * @param Player[] $players
     * @param bool[] $isWaitings
     */
    function __construct(array $players, array $isWaitings) {
        parent::__construct($players, RoundResultType::create(RoundResultType::EXHAUSTIVE_DRAW));
        $valid = count($players) == count($isWaitings);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->players = $players;
        $this->isWaitings = $isWaitings;
    }

    /**
     * @param Player $player
     * @return PointDelta
     */
    function getPointDeltaInt(Player $player) {
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

    /**
     * @return Player
     */
    function isKeepDealer() {
        return $this->isWaiting($this->getOriginDealerPlayer());
    }

    function isWaiting(Player $player) {
        $i = array_search($player, $this->players, false);
        if ($i === false) {
            throw new \InvalidArgumentException();
        }
        return $this->isWaitings[$i];
    }

    function getIsWaitingCount() {
        $isWaitingList = new ArrayList($this->isWaitings);
        $targetValue = true;
        return $isWaitingList->getCount(Utils::toPredicate($targetValue));
    }

    function getNotWaitingCount() {
        return count($this->isWaitings) - $this->getIsWaitingCount();
    }
}