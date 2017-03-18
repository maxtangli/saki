<?php

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\Tile\TileList;
use Saki\Win\Result\ResultType;

class DrawTest extends \SakiTestCase {
    function testNotFourKongDrawBySamePlayer() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1s; discard E 1s; passAll'
        );
        $this->assertPrivate('S');
    }

    function testFourKongDrawByConcealedKong() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s1m; concealedKong E 1s1s1s1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s1s1s1s; discard N 1m; passAll'
        );
        $this->assertOver(ResultType::FOUR_KONG_DRAW);
    }

    function testFourKongDrawByExtendKong() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1m; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s1s1s1s; discard N 1m',
            'mockHand E 111m1p; pung E 1m1m; extendKong E 1m 111m; passAll; discard E 1p; passAll'
        );
        $this->assertOver(ResultType::FOUR_KONG_DRAW);
    }

    function testFourKongDrawByKong() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s1m; concealedKong E 1s1s1s1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m',
            'mockHand E 1111m; kong E 1m1m1m'
        );
        $this->assertPrivate();

        $round->process('discard E 1m; passAll');
        $this->assertOver(ResultType::FOUR_KONG_DRAW);
    }

    function testNineNineDraw() {
        $round = $this->getInitRound();

        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTermOrHonour());

        $round->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertOver(ResultType::NINE_NINE_DRAW);

        $round->process('toNextRound');
        $this->assertPrivate();
    }

    function testExhaustiveDraw() {
        $round = $this->getInitRound();
        $round->process('mockHand E 123456789m12355p; riichi E 5p');
        for ($phase = $round->getPhaseState()->getPhase(); $phase != Phase::createOver(); $phase = $round->getPhaseState()->getPhase()) {
            $round->process('skip 1');
        }
        $this->assertOver(ResultType::EXHAUSTIVE_DRAW);
        $this->assertPoints([25000 - 1000 + 3000, 25000 - 1000, 25000 - 1000, 25000 - 1000]);
    }

    function testNagashiManganDrawSingle() {
        $round = $this->getInitRound();
        $round->process('mockHand E 123456789m12355p; riichi E 5p');
        $this->processNagashiMangan($round, ['N']);
        $this->assertOver(ResultType::NAGASHIMANGAN_DRAW, true);
        $this->assertPoints([25000 - 1000 - 4000, 25000 - 2000, 25000 - 2000, 25000 + 8000]);
    }

    function testNagashiManganDrawDouble() {
        $round = $this->getInitRound();
        $this->processNagashiMangan($round, ['W', 'N']);
        $this->assertOver(ResultType::NAGASHIMANGAN_DRAW, false);
        $this->assertPoints([25000 - 4000 - 4000, 25000 - 2000 - 2000, 25000 + 8000 - 2000, 25000 + 8000 - 2000]);
    }

    function testNagashiManganDrawTriple() {
        $round = $this->getInitRound();
        $this->processNagashiMangan($round, ['S', 'W', 'N']);
        $this->assertOver(ResultType::NAGASHIMANGAN_DRAW, false);
        $this->assertPoints([25000 - 4000 - 4000 - 4000, 25000 + 8000 - 2000 - 2000, 25000 + 8000 - 2000 - 2000, 25000 + 8000 - 2000 - 2000]);
    }

    function testNagashiManganDrawAll() {
        $round = $this->getInitRound();
        $this->processNagashiMangan($round, ['E', 'S', 'W', 'N']);
        $this->assertOver(ResultType::NAGASHIMANGAN_DRAW, false);
        $this->assertPoints([25000, 25000, 25000, 25000]);
    }

    function testNagashiManganAllowClaim() {
        $round = $this->getInitRound();
        $round->process('mockHand E 2222m; concealedKong E 2222m');
        $this->processNagashiMangan($round, ['E']);
        $this->assertOver(ResultType::NAGASHIMANGAN_DRAW);
    }

    function testNagashiManganNotAllowClaimed() {
        $round = $this->getInitRound();
        // remind: "mockHand S E" to avoid swap calling
        $round->process('mockHand E 1m; discard E 1m; mockHand S 23mE; chow S 23m; discard S E');
        $this->processNagashiMangan($round, ['E']);
        $this->assertOver(ResultType::EXHAUSTIVE_DRAW);
    }

    private function processNagashiMangan(Round $round, array $actors) {
        while (($phase = $round->getPhaseState()->getPhase()) != Phase::createOver()) {
            $actorString = $round->getCurrentSeatWind()->__toString();
            if ($phase->isPrivate() && in_array($actorString, $actors)) {
                $round->process("mockHand $actorString 1m; discard $actorString 1m");
            } else { // other private or public
                $round->process('skip 1');
            }
        }
    }

    function testFourRiichiDraw() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 123456789m12357s; riichi E 7s; passAll',
            'mockHand S 123456789m12357s; riichi S 7s; passAll',
            'mockHand W 123456789m12357s; riichi W 7s; passAll',
            'mockHand N 123456789m12357s; riichi N 7s; passAll'
        );
        $this->assertOver(ResultType::FOUR_REACH_DRAW);
    }

    function testFourWindDraw() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E E; discard E E; passAll',
            'mockHand S E; discard S E; passAll',
            'mockHand W E; discard W E; passAll',
            'mockHand N E; discard N E; passAll'
        );
        $this->assertOver(ResultType::FOUR_WIND_DRAW);
    }
}
