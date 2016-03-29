<?php

use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

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
        $this->assertTrue(Meld::validString('(11m)'));
        $this->assertTrue(Meld::validString('(123m)'));
        // fromString
        $meld = Meld::fromString('(111m)');
        $this->assertTrue($meld->isConcealed());
        $meld = Meld::fromString('(1111m)');
        $this->assertTrue($meld->isConcealed());
    }

    function testEquals() {
        $mNotConcealed = Meld::fromString('123s');
        $mNotConcealed2 = Meld::fromString('123s');
        $this->assertTrue($mNotConcealed == $mNotConcealed2);
        $this->assertTrue($mNotConcealed->equals($mNotConcealed2, true));
        $this->assertTrue($mNotConcealed->equals($mNotConcealed2, false));

        $mConcealed = Meld::fromString('(123s)');
        $this->assertFalse($mNotConcealed == $mConcealed);
        $this->assertFalse($mNotConcealed->equals($mConcealed, true));
        $this->assertTrue($mNotConcealed->equals($mConcealed, false));

        // array

        $meldArray = new ArrayList([$mNotConcealed]);
        $this->assertTrue($meldArray->valueExist($mNotConcealed));
        $this->assertTrue($meldArray->valueExist($mNotConcealed, Meld::getEqualsCallback(true)));
        $this->assertTrue($meldArray->valueExist($mNotConcealed, Meld::getEqualsCallback(false)));

        $this->assertFalse($meldArray->valueExist($mConcealed));
        $this->assertFalse($meldArray->valueExist($mConcealed, Meld::getEqualsCallback(true)));
        $this->assertTrue($meldArray->valueExist($mConcealed, Meld::getEqualsCallback(false)));
    }

    function testAddKong() {
        // canPlusKong
        $meld = new \Saki\Meld\Meld(TileList::fromString('111m'), \Saki\Meld\TripleMeldType::getInstance());
        $this->assertTrue($meld->canToTargetMeld(\Saki\Tile\Tile::fromString('1m'), \Saki\Meld\QuadMeldType::getInstance()));
        $this->assertFalse($meld->canToTargetMeld(Tile::fromString('1s'), \Saki\Meld\QuadMeldType::getInstance()));
        // plusKong get Quad
        $meld2 = $meld->toTargetMeld(\Saki\Tile\Tile::fromString('1m'), \Saki\Meld\QuadMeldType::getInstance());
        $this->assertSame('1111m', $meld2->__toString());
        $this->assertEquals(\Saki\Meld\QuadMeldType::getInstance(), $meld2->getMeldType());
    }

    /**
     * @depends      testAddKong
     * @dataProvider addKongIsConcealedProvider
     */
    function testAddKongIsConcealed($before, $forceConcealed, $after) {
        $m1 = new Meld(TileList::fromString('111m'), null, $before);
        $m2 = $m1->toTargetMeld(Tile::fromString('1m'), \Saki\Meld\QuadMeldType::getInstance(), $forceConcealed);
        $this->assertEquals($after, $m2->isConcealed());
    }

    function addKongIsConcealedProvider() {
        return [
            [true, null, true],
            [false, null, false],
            [true, false, false],
            [false, false, false],
            [true, true, true],
            [false, true, true],
        ];
    }

    // --- weak ---


}