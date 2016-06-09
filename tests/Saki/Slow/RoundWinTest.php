<?php

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Result\ResultType;

class RoundWinTest extends SakiTestCase {
    function testTsumo() {
        $r = $this->getInitRound();

        // test over phase
        $r->process('mockHand E 123m456m789m123s55s; tsumo E');
        $this->assertEquals(Phase::createOver(), $r->getAreas()->getPhaseState()->getPhase());
        $this->assertCount(1, $r->getAreas()->getWall()->getDoraFacade()->getOpenedUraDoraIndicators());

        // test toNextRound
        $r->toNextRound();
        $this->assertEquals(Phase::createPrivate(), $r->getAreas()->getPhaseState()->getPhase());
        $this->assertEquals(SeatWind::createEast(), $r->getAreas()->getDealerArea()->getPlayer()->getInitialSeatWind());
    }

    function testRon() {
        $this->getInitRound()->process(
            'mockHand E 4s; discard E 4s',
            'mockHand S 123m456m789m23s55s; ron S'
        );
        $this->assertResultType(ResultType::WIN_BY_OTHER);
    }

    function testMultiRon() {
        // todo
    }

    function testGameOver() {
        // to E Round N Dealer
        $r = new Round();
        $r->roll(false);
        $r->roll(false);
        $r->roll(false);
        $areas = $r->getAreas();
        $area = $areas->getCurrentArea();
        $pointHolder = $areas->getPointHolder();
        // todo replace reset() by debugReset()

        // E Player tsumo, but point not over 30000
        $area->setHand(
            $area->getHand()->toHand(TileList::fromString('13m456m789m123s55s'), null, Tile::fromString('2m'))
        );
        $r->getProcessor()->process('tsumo E');
        $pointHolder->setPoint(SeatWind::fromString('E'), 25000);
        $this->assertFalse($r->getAreas()->getPhaseState()->isGameOver($r));

        // point over 30000
        $pointHolder->setPoint(SeatWind::fromString('E'), 29999);
        $this->assertFalse($r->getAreas()->getPhaseState()->isGameOver($r));

        $pointHolder->setPoint(SeatWind::fromString('E'), 30000);
        $this->assertTrue($r->getAreas()->getPhaseState()->isGameOver($r));
    }
}
