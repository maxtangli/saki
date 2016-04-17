<?php
namespace Saki\Win\Point;

/** todo support 2 or 3 players cases
 * Calculate Winner or Loser's point change without multi-win case.
 * @package Saki\Result
 */
class PointTableItem {
    private $basePoint;

    /**
     * @param int $basePoint
     */
    function __construct(int $basePoint) {
        $this->basePoint = $basePoint;;
    }

    /**
     * @return int
     */
    function getBasePoint() {
        return $this->basePoint;
    }

    /**
     * @param bool $isWinBySelf
     * @param bool $winnerIsDealer
     * @return int A positive number means winner's point change.
     */
    function getWinnerPointChange(bool $isWinBySelf, bool $winnerIsDealer) {
        if ($isWinBySelf && $winnerIsDealer) {
            return 3 * -$this->getLoserPointChange($isWinBySelf, $winnerIsDealer, false);
        }

        // - 胜者收入：基本点x(胜者为庄家?6:4)。
        $ratio = $winnerIsDealer ? 6 : 4;
        $rawPoint = $this->getBasePoint() * $ratio;
        return $this->util_toFinalPoint($rawPoint);
    }

    /**
     * @param bool $isWinBySelf
     * @param bool $winnerIsDealer
     * @param bool $loserIsDealer
     * @return int A negative number means loser's point change.
     */
    function getLoserPointChange(bool $isWinBySelf, bool $winnerIsDealer, bool $loserIsDealer) {
        $ratioMap = [
            // $isWinBySelf =>
            //  $winnerIsDealer => $loserIsDealer => $ratio
            true => [
                true => [true => 'error', false => 2,],
                false => [true => 2, false => 1,],
            ],
            false => [
                true => [true => 'error', false => 'all',],
                false => [true => 'all', false => 'all',],
            ],
        ];

        $ratio = $ratioMap[$isWinBySelf][$winnerIsDealer][$loserIsDealer];
        if (is_int($ratio)) {
            $rawPoint = -$this->getBasePoint() * $ratio;
            return $this->util_toFinalPoint($rawPoint);
        } elseif ($ratio === 'error') {
            throw new \InvalidArgumentException(
                sprintf('Invalid argument $isWinBySelf[%s], $winnerIsDealer[%s], $loserIsDealer[%s].'
                    , $isWinBySelf, $winnerIsDealer, $loserIsDealer)
            );
        } elseif ($ratio === 'all') {
            return -$this->getWinnerPointChange($isWinBySelf, $winnerIsDealer);
        } else {
            throw new \LogicException();
        }
    }

    /**
     * 切り上げ. e.x.640->700
     * @param int $rawPoint
     * @return int
     */
    protected function util_toFinalPoint(int $rawPoint) {
        $sgn = $rawPoint / abs($rawPoint);
        return intval(ceil(abs($rawPoint) / 100) * 100) * $sgn;
    }

    /** todo remove
     * @param bool $receiverIsDealer
     * @param bool $winBySelf
     * @return int
     */
    function getReceivePoint(bool $receiverIsDealer, bool $winBySelf) {
        if (!$winBySelf) {
            return $this->getPayPoint($receiverIsDealer, $winBySelf, false);
        } else {
            if ($receiverIsDealer) {
                return 3 * $this->getPayPoint($receiverIsDealer, $winBySelf, false);
            } else {
                return 2 * $this->getPayPoint($receiverIsDealer, $winBySelf, false)
                + $this->getPayPoint($receiverIsDealer, $winBySelf, true);
            }
        }
    }

    /** todo remove
     * @param bool $receiverIsDealer
     * @param bool $winBySelf
     * @param bool $payerIsDealer
     * @return int
     */
    function getPayPoint(bool $receiverIsDealer, bool $winBySelf, bool $payerIsDealer) {
        $valid = !($receiverIsDealer && $payerIsDealer);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        if ($winBySelf) {
            $ratio = ($receiverIsDealer || $payerIsDealer) ? 2 : 1;
        } else {
            $ratio = $receiverIsDealer ? 6 : 4;
        }
        $point = $this->getBasePoint() * $ratio;

        $point = intval(ceil($point / 100) * 100); // 切り上げ. e.x.640->700
        return $point;
    }
}