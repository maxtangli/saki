<?php

use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MeldTest extends PHPUnit_Framework_TestCase {

    function testCreate() {
        // new by MeldType
        $meldType = \Saki\Meld\PairMeldType::getInstance();
        $meld = new \Saki\Meld\Meld(TileList::fromString('11m', false), $meldType);
        $this->assertEquals($meldType, $meld->getMeldType());

        // new by analyze MeldType
        $meld = new \Saki\Meld\Meld(TileList::fromString('11m'));
        $this->assertEquals(\Saki\Meld\PairMeldType::getInstance(), $meld->getMeldType());
        $this->assertEquals('11m', $meld->__toString());

        // validString
        $this->assertTrue(Meld::validString('11m'));
        $this->assertFalse(Meld::validString('14m'));

        // fromString
        $meld = \Saki\Meld\Meld::fromString('11m');
        $this->assertEquals(\Saki\Meld\PairMeldType::getInstance(), $meld->getMeldType());
        $this->assertEquals('11m', $meld->__toString());
    }

    function testConcealed() {
        // validString
        $this->assertTrue(Meld::validString('(111m)'));
        $this->assertTrue(Meld::validString('(1111m)'));
        $this->assertFalse(Meld::validString('(11m)'));
        $this->assertFalse(Meld::validString('(123m)'));
        // fromString
        $meld = Meld::fromString('(111m)');
        $this->assertTrue($meld->isConcealed());
        $meld = Meld::fromString('(1111m)');
        $this->assertTrue($meld->isConcealed());
    }

    function testAddKong() {
        // canPlusKong
        $meld = new \Saki\Meld\Meld(TileList::fromString('111m'), \Saki\Meld\TripleMeldType::getInstance());
        $this->assertTrue($meld->canToTargetMeld(\Saki\Tile\Tile::fromString('1m'), \Saki\Meld\QuadMeldType::getInstance()));
        $this->assertFalse($meld->canToTargetMeld(Tile::fromString('1s'), \Saki\Meld\QuadMeldType::getInstance()));
        // plusKong get Quad
        $meld2 = $meld->toTargetMeld(\Saki\Tile\Tile::fromString('1m'), \Saki\Meld\QuadMeldType::getInstance(), null);
        $this->assertSame('1111m', $meld2->__toString());
        $this->assertEquals(\Saki\Meld\QuadMeldType::getInstance(), $meld2->getMeldType());
    }

    /**
     * @depends      testAddKong
     * @dataProvider addKongIsExposedProvider
     */
    function testAddKongIsExposed($before, $forceExposed, $after) {
        $m1 = new Meld(TileList::fromString('111m'), null, $before);
        $m2 = $m1->toTargetMeld(Tile::fromString('1m'), \Saki\Meld\QuadMeldType::getInstance(), $forceExposed);
        $this->assertEquals($after, $m2->isExposed());
    }

    function addKongIsExposedProvider() {
        return [
            [true, null, true],
            [false, null, false],
            [true, false, false],
            [false, false, false],
            [true, true, true],
            [false, true, true],
        ];
    }
}