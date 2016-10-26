<?php

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\PairMeldType;
use Saki\Game\Meld\QuadMeldType;
use Saki\Game\Meld\ThirteenOrphanMeldType;
use Saki\Game\Meld\WeakThirteenOrphanMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;

class MeldTest extends \SakiTestCase {
    function testCreate() {
        // new by MeldType
        $meldType = PairMeldType::create();
        $meld = new Meld([Tile::fromString('1m'), Tile::fromString('1m')], $meldType);
        $this->assertEquals($meldType, $meld->getMeldType());

        // new by analyze MeldType
        $meld = new Meld([Tile::fromString('1m'), Tile::fromString('1m')]);
        $this->assertEquals(PairMeldType::create(), $meld->getMeldType());
        $this->assertEquals('11m', $meld->__toString());

        // validString
        $this->assertTrue(Meld::validString('11m'));
        $this->assertFalse(Meld::validString('14m'));

        // fromString
        $meld = Meld::fromString('11m');
        $this->assertEquals(PairMeldType::create(), $meld->getMeldType());
        $this->assertEquals('11m', $meld->__toString());

        // thirteen orphan
        $meld = new Meld(TileList::fromString('119m19p19sESWNCPF')->toArray(), ThirteenOrphanMeldType::create());
        $this->assertEquals(ThirteenOrphanMeldType::create(), $meld->getMeldType());

        $meld = Meld::fromString('119m19p19sESWNCPF');
        $this->assertEquals(ThirteenOrphanMeldType::create(), $meld->getMeldType());
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

    function testAddKong() {
        // canExtendKong
        $meld = Meld::fromString('111m');
        $this->assertTrue($meld->canToTargetMeld(Tile::fromString('1m'), QuadMeldType::create()));
        $this->assertFalse($meld->canToTargetMeld(Tile::fromString('1s'), QuadMeldType::create()));
        // extendKong get Quad
        $meld2 = $meld->toTargetMeld(Tile::fromString('1m'), QuadMeldType::create());
        $this->assertSame('1111m', $meld2->__toString());
        $this->assertEquals(QuadMeldType::create(), $meld2->getMeldType());
    }

    /**
     * @depends      testAddKong
     * @dataProvider addKongIsConcealedProvider
     */
    function testAddKongIsConcealed($before, $forceConcealed, $after) {
        $m1 = new Meld(TileList::fromString('111m')->toArray(), null, $before);
        $m2 = $m1->toTargetMeld(Tile::fromString('1m'), QuadMeldType::create(), $forceConcealed);
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

    function testWeakThirteenOrphan() {
        $mt = WeakThirteenOrphanMeldType::create();

        $this->assertArrayList('19m19p19sESWNCPF', $mt->getWaiting(TileList::fromString('19m19p19sESWNCPF')));
        $this->assertArrayList('9m', $mt->getWaiting(TileList::fromString('11m19p19sESWNCPF')));

        $this->assertFalse($mt->valid(TileList::fromString('111m9p19sESWNCPF')));
    }
}