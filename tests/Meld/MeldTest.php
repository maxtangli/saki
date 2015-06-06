<?php

use Saki\TileList;

class MeldTest extends PHPUnit_Framework_TestCase {

    function testOverall() {
        $meldType = \Saki\Meld\EyesMeldType::getInstance();
        $meld = new \Saki\Meld\Meld(TileList::fromString('11m'), $meldType);
        $this->assertEquals($meldType, $meld->getMeldType());
    }
}