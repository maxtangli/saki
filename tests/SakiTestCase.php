<?php

use Saki\Game\Phase;
use Saki\Game\PointItem;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Tile\Tile;
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

    //region get Round
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
    //endregion

    //region Areas
    function assertHand(string $private = null, string $melded = null, string $targetTile = null,
                        string $seatWind = null) {
        $r = $this->getCurrentRound();
        $actualSeatWind = $seatWind !== null ? SeatWind::fromString($seatWind) : $r->getAreas()->getCurrentSeatWind();
        $hand = $r->getAreas()->getArea($actualSeatWind)->getHand();

        if ($private !== null) { // == vs isLocked
            $this->assertEquals($private, $hand->getPrivate()->__toString());
        }

        if ($melded !== null) { // == vs isLocked
            $this->assertEquals($melded, $hand->getMelded()->__toString());
        }

        if ($targetTile !== null) {
            $this->assertEquals(Tile::fromString($targetTile), $hand->getTarget()->getTile());
        }
    }

    function assertLastOpen(string $tile) {
        $r = $this->getCurrentRound();
        $openHistory = $r->getAreas()->getOpenHistory();
        $this->assertEquals($tile, $openHistory->getLastOpen()->getTile());
    }

    function assertHasClaim(Turn $turn) {
        $r = $this->getCurrentRound();
        $claimHistory = $r->getAreas()->getClaimHistory();
        $this->assertTrue($claimHistory->hasClaim($turn));
    }

    function assertHasNotClaim(Turn $turn) {
        $r = $this->getCurrentRound();
        $claimHistory = $r->getAreas()->getClaimHistory();
        $this->assertFalse($claimHistory->hasClaim($turn));
    }

    function assertCurrentTurnChanged(string $seatWind = null, Turn $laterThanOldTurn = null) {
        $r = $this->getCurrentRound();
        $currentTurn = $r->getAreas()->getTurn();

        if ($seatWind !== null) {
            $this->assertEquals(SeatWind::fromString($seatWind), $currentTurn->getSeatWind());
        }

        if ($laterThanOldTurn !== null) {
            $this->assertTrue($currentTurn->isAfter($laterThanOldTurn));
        }
    }

    function assertCurrentTurnNotChanged(Turn $turn) {
        $r = $this->getCurrentRound();
        $currentTurn = $r->getAreas()->getTurn();
        $this->assertEquals($turn, $currentTurn);
    }
    //endregion

    //region Phase
    function assertPrivate(string $currentSeatWind = null) {
        $this->assertPhase(Phase::PRIVATE_PHASE, $currentSeatWind);
    }

    function assertPublic(string $currentSeatWind = null) {
        $this->assertPhase(Phase::PUBLIC_PHASE, $currentSeatWind);
    }

    protected function assertPhase(int $phaseValue, string $currentSeatWind = null) {
        $r = $this->getCurrentRound();

        $currentPhase = $r->getAreas()->getPhaseState()->getPhase();
        $this->assertEquals(Phase::create($phaseValue), $currentPhase);

        if ($currentSeatWind !== null) {
            $currentSeatWind = $r->getAreas()->getCurrentSeatWind();
            $this->assertEquals(SeatWind::fromString($currentSeatWind), $currentSeatWind);
        }
    }
    //endregion

    //region Result
    function assertResultType(int $resultTypeValue) {
        $expected = ResultType::create($resultTypeValue);
        $actual = $this->getCurrentRound()->getAreas()->getPhaseState()->getResult()->getResultType();
        $this->assertEquals($expected, $actual);
    }

    function assertPointItem(string $seatWindString, int $point, int $rank, PointItem $pointItem) {
        $this->assertEquals(SeatWind::fromString($seatWindString), $pointItem->getSeatWind());
        $this->assertEquals($point, $pointItem->getPoint());
        $this->assertEquals($rank, $pointItem->getRank());
    }

    function assertPointList(array $expectItems, PointList $pointList) {
        foreach ($expectItems as $i => list($seatWindString, $point, $rank)) {
            $pointItem = $pointList[$i];
            $this->assertPointItem($seatWindString, $point, $rank, $pointItem);
        }
    }
    //endregion
}
