<?php

use Saki\Game\Phase;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Result\ResultType;

class RoundWinTest extends \SakiTestCase {
    function testTsumo() {
        $round = $this->getInitRound();

        // test over phase
        $round->process('mockHand E 123m456m789m123s55s; tsumo E');
        $this->assertEquals(Phase::createOver(), $round->getPhaseState()->getPhase());
        $this->assertCount(1, $round->getWall()->getDoraFacade()->getOpenedUraDoraIndicators());

        // test toNextRound
        $round->toNextRound();
        $this->assertEquals(Phase::createPrivate(), $round->getPhaseState()->getPhase());
        $this->assertEquals(SeatWind::createEast(), $round->getDealerArea()->getPlayer()->getInitialSeatWind());
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
        $round = $this->getInitRound();
        $round->roll(false);
        $round->roll(false);
        $round->roll(false);

        $area = $round->getCurrentArea();
        $pointHolder = $round->getPointHolder();
        // todo replace reset() by debugReset()

        // E Player tsumo, but point not over 30000
        $area->setHand(
            $area->getHand()->toHand(TileList::fromString('13m456m789m123s55s'), null, Tile::fromString('2m'))
        );
        $round->process('tsumo E');
        $pointHolder->setPoint(SeatWind::fromString('E'), 25000);
        $this->assertFalse($round->getPhaseState()->isGameOver($round));

        // point over 30000
        $pointHolder->setPoint(SeatWind::fromString('E'), 29999);
        $this->assertFalse($round->getPhaseState()->isGameOver($round));

        $pointHolder->setPoint(SeatWind::fromString('E'), 30000);
        $this->assertTrue($round->getPhaseState()->isGameOver($round));
    }
}
