<?php

use Saki\TileList;
use Saki\Meld\Meld;

class MeldTest extends PHPUnit_Framework_TestCase {

    function testCreate() {
        // new by MeldType
        $meldType = \Saki\Meld\EyesMeldType::getInstance();
        $meld = new \Saki\Meld\Meld(TileList::fromString('11m', false), $meldType);
        $this->assertEquals($meldType, $meld->getMeldType());

        // new by analyze MeldType
        $meld = new \Saki\Meld\Meld(TileList::fromString('11m'));
        $this->assertEquals(\Saki\Meld\EyesMeldType::getInstance(), $meld->getMeldType());
        $this->assertEquals('11m', $meld->__toString());

        // validString
        $this->assertTrue(Meld::validString('11m'));
        $this->assertFalse(Meld::validString('1m'));

        // fromString
        $meld = \Saki\Meld\Meld::fromString('11m');
        $this->assertEquals(\Saki\Meld\EyesMeldType::getInstance(), $meld->getMeldType());
        $this->assertEquals('11m', $meld->__toString());
    }

    function testAddKong() {
        $meld = new \Saki\Meld\Meld(TileList::fromString('111m'), \Saki\Meld\TripletMeldType::getInstance());
        $this->assertTrue($meld->canAddKong(\Saki\Tile::fromString('1m')));
        $this->assertFalse($meld->canAddKong(\Saki\Tile::fromString('1s')));

        $meld2 = $meld->getAddedKongMeld(\Saki\Tile::fromString('1m'));
        $this->assertSame('1111m', $meld2->__toString());
        $this->assertEquals(\Saki\Meld\KongMeldType::getInstance(), $meld2->getMeldType());
    }
}