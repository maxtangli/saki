<?php

use Saki\TileList;

class MeldTypeTest extends PHPUnit_Framework_TestCase {

    function testSingle() {
        $mt = \Saki\Meld\SingleMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('1m')));
        $this->assertFalse($mt->valid(TileList::fromString('11m')));
    }

    function testEyes() {
        $mt = \Saki\Meld\EyesMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('11m')));
        $this->assertFalse($mt->valid(TileList::fromString('111m')));
    }

    function testSequence() {
        $mt = \Saki\Meld\SequenceMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('123m')));
        $this->assertTrue($mt->valid(TileList::fromString('132m')));
        $this->assertTrue($mt->valid(TileList::fromString('213m')));
        $this->assertTrue($mt->valid(TileList::fromString('231m')));
        $this->assertTrue($mt->valid(TileList::fromString('312m')));
        $this->assertTrue($mt->valid(TileList::fromString('321m')));
        $this->assertFalse($mt->valid(TileList::fromString('12m3s')));
        $this->assertFalse($mt->valid(TileList::fromString('121m')));
    }

    function testTriplet() {
        $mt = \Saki\Meld\TripletMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('111m')));
        $this->assertFalse($mt->valid(TileList::fromString('11m1s')));
        $this->assertFalse($mt->valid(TileList::fromString('11m')));
    }
}