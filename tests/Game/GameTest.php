<?php

use Saki\Game\Round;
use Saki\Game\PlayerList;
use Saki\Game\Wall;

class GameTest extends PHPUnit_Framework_TestCase {

    function testOverall() {

    }

    function testSerialize() {

        $g1 = new \Saki\Game\Game(4,40000);

        $g2 = unserialize(serialize($g1));
        $this->assertEquals($g1, $g2);

//        session_start();
//        $_SESSION['data'] = $g1;
//        $g2 = $_SESSION['data'];
//        $this->assertEquals($g1->getCurrentRound()->getCurrentTurn(), $g2->getCurrentRound()->getCurrentTurn());
//        $this->assertEquals($g1, $g2);
    }
}
