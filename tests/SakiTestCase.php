<?php

use Saki\Game\PointItem;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Win\Point\PointList;
use Saki\Win\Result\ResultType;

class SakiTestCase extends \PHPUnit_Framework_TestCase {
    //region override
    static function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        if ($message === '' && is_object($expected) && is_object($actual)) {
            $message = sprintf(
                'Failed asserting that two objects are equal, $expected[%s] but $actual[%s].',
                $expected, $actual
            );
        }
        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    //endregion

    private static $r;

    /**
     * @param PrevailingStatus|null $rebugResetData
     * @return Round
     */
    static function getInitRound(PrevailingStatus $rebugResetData = null) {
        self::$r = self::$r ?? new Round();
        self::$r->debugInit($rebugResetData ?? PrevailingStatus::createFirst());
        return self::$r;
    }

    /**
     * @return Round
     */
    static function getCurrentRound() {
        return self::$r ?? self::getInitRound();
    }
    
    function assertResultType(int $resultTypeValue) {
        $expected = ResultType::create($resultTypeValue);
        $actual = $this->getCurrentRound()->getPhaseState()->getResult()->getResultType();
        $this->assertEquals($expected, $actual);
    }
    
    function assertPointItem(string $seatWindString, int $point, int $rank, PointItem $pointItem) {
        $this->assertEquals(SeatWind::fromString($seatWindString), $pointItem->getSeatWind());
        $this->assertEquals($point, $pointItem->getPoint());
        $this->assertEquals($rank, $pointItem->getRank());
    }

    /**
     * @param array $expectItems
     * @param PointList $pointList
     */
    function assertPointList(array $expectItems, PointList $pointList) {
        foreach ($expectItems as $i => list($seatWindString, $point, $rank)) {
            $pointItem = $pointList[$i];
            $this->assertPointItem($seatWindString, $point, $rank, $pointItem);
        }
    }
}
