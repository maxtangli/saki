<?php
//
//use Saki\Game\PlayerList;
//
//class PlayerListTest extends PHPUnit_Framework_TestCase {
//    function testInitialState() {
//        $m = new PlayerList(4, 25000);
//        foreach ($m as $k => $p) {
//            $this->assertEquals($k + 1, $p->getNo());
//        }
//        list($p1, $p2, $p3, $p4) = $m->toArray();
//        $this->assertEquals($p1, $m->getCurrentPlayer());
//        $this->assertEquals($p1, $m->getDealerPlayer());
//
//        // current/dealer offset
//        for ($i = 0; $i < 4; ++$i) {
//            $this->assertEquals($m[$i], $m->getCurrentOffsetPlayer($i));
//            $this->assertEquals($m[$i], $m->getDealerOffsetPlayer($i));
//        }
//    }
//
//    function testToNext() {
//        $m = new PlayerList(4, 10000);
//        list($p1, $p2, $p3, $p4) = $m->toArray();
//        $m->toPlayer($p3);
//        $expectedPlayer = [$p3, $p4, $p1, $p2];
//        for ($i = 0; $i < count($expectedPlayer); ++$i) {
//            $this->assertSame($expectedPlayer[$i], $m->getCurrentPlayer(), sprintf('[%s] vs [%s]', $expectedPlayer[$i], $m->getCurrentPlayer()));
//            // removed: $this->assertSame(1, $m->getCurrentPlayer()->getLocalTurn());
//            $m->toNextPlayer();
//        }
//
//        // current/dealer offset
//        $m->reset($p3);
//        $this->assertEquals($p3, $m->getCurrentPlayer());
//        $this->assertEquals($p3, $m->getDealerPlayer());
//        for ($i = 0; $i < 4; ++$i) {
//            $this->assertEquals($expectedPlayer[$i], $m->getCurrentOffsetPlayer($i), $i);
//            $this->assertEquals($expectedPlayer[$i], $m->getDealerOffsetPlayer($i), $i);
//        }
//    }
//
//    function testGlobalTurn() {
//        $m = new PlayerList(4, 10000);
//        $this->assertEquals(1, $m->getGlobalTurn());
//
//        foreach($m as $p) {
//            $m->toPlayer($p);
//            $this->assertEquals(1, $m->getGlobalTurn());
//        }
//
//        list($p1, $p2, $p3, $p4) = $m->toArray();
//        $m->toPlayer($p1);
//        $this->assertEquals(2, $m->getGlobalTurn());
//    }
//}
