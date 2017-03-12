<?php
namespace Saki\Win\Point;

/**
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
     * @param bool $isTsumo
     * @param bool $winnerIsDealer
     * @return int A positive number means winner's point change.
     */
    function getWinnerPointChange(bool $isTsumo, bool $winnerIsDealer) {
        if ($isTsumo) {
            return $winnerIsDealer
                ? 3 * -$this->getLoserPointChange(true, $winnerIsDealer, false)
                : 2 * -$this->getLoserPointChange(true, $winnerIsDealer, false)
                + 1 * -$this->getLoserPointChange(true, $winnerIsDealer, true);
        } else {
            $notUsed = !$winnerIsDealer;
            return -$this->getLoserPointChange(false, $winnerIsDealer, $notUsed);
        }
    }

    /**
     * @param bool $isTsumo
     * @param bool $winnerIsDealer
     * @param bool $loserIsDealer
     * @return int A negative number means loser's point change.
     */
    function getLoserPointChange(bool $isTsumo, bool $winnerIsDealer, bool $loserIsDealer) {
        $ratioMap = [
            // $isTsumo =>
            //  $winnerIsDealer => $loserIsDealer => $ratio
            true => [
                true => [true => 'error', false => 2,],
                false => [true => 2, false => 1,],
            ],
            false => [
                true => [true => 'error', false => 6,],
                false => [true => 4, false => 4,],
            ],
        ];

        $ratio = $ratioMap[$isTsumo][$winnerIsDealer][$loserIsDealer];
        if (is_int($ratio)) {
            $rawPoint = -$this->getBasePoint() * $ratio;
            return $this->util_toFinalPoint($rawPoint);
        } elseif ($ratio === 'error') {
            throw new \InvalidArgumentException(
                sprintf('Invalid argument $isTsumo[%s], $winnerIsDealer[%s], $loserIsDealer[%s].'
                    , $isTsumo, $winnerIsDealer, $loserIsDealer)
            );
        } else {
            throw new \LogicException();
        }
    }

    /**
     * 切り上げ. e.g.640->700
     * @param int $rawPoint
     * @return int
     */
    protected function util_toFinalPoint(int $rawPoint) {
        $sgn = $rawPoint / abs($rawPoint);
        return intval(ceil(abs($rawPoint) / 100) * 100) * $sgn;
    }
}