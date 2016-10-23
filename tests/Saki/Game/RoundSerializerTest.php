<?php

use Saki\Game\SeatWind;
use Saki\Play\Privilege;
use Saki\Play\RoundSerializer;

class RoundSerializerTest extends \SakiTestCase {
    function testJson() {
        $round = $this->getInitRound();
        $privilege = Privilege::createPlayer(SeatWind::createEast());
        $serializer = new RoundSerializer($round, $privilege);


        // todo test initial
        $a = $serializer->toAllJson();
        $this->assertCount(4, $a['areas']);

        // todo test round over
        $round->process('mockHand E 123456789m12344p; tsumo E');
        $a = $serializer->toAllJson();
        $this->assertTrue($a['result']['isRoundOver']);
        $this->assertNotEmpty($a['result']['winReports']);

        // todo test game over

    }
}