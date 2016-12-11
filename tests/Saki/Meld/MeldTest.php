<?php

use Saki\Game\Claim;
use Saki\Game\Meld\Meld;
use Saki\Game\Meld\PairMeldType;
use Saki\Game\Meld\QuadMeldType;
use Saki\Game\Meld\ThirteenOrphanMeldType;
use Saki\Game\Meld\WeakThirteenOrphanMeldType;
use Saki\Game\Relation;
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\TargetType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Turn;

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
     * @dataProvider provideAddKongIsConcealed
     */
    function testAddKongIsConcealed($before, $forceConcealed, $after) {
        $m1 = new Meld(TileList::fromString('111m')->toArray(), null, $before);
        $m2 = $m1->toTargetMeld(Tile::fromString('1m'), QuadMeldType::create(), $forceConcealed);
        $this->assertEquals($after, $m2->isConcealed());
    }

    function provideAddKongIsConcealed() {
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

    /**
     * @dataProvider provideToJsonOther
     */
    function testToJsonOther(array $expected, string $meldString, string $tileString, string $relationString) {
        $self = SeatWind::createEast();
        $meld = Meld::fromString($meldString);
        $otherTarget = new Target(
            Tile::fromString($tileString),
            TargetType::create(TargetType::DISCARD),
            Relation::fromString($relationString)->toOther($self)
        );
        $claim = Claim::create(
            $self,
            Turn::createFirst(),
            $meld->toArray(),
            $meld->getMeldType(),
            false,
            $otherTarget
        );
        $this->assertEquals($expected, $claim->toJson());
    }

    function provideToJsonOther() {
        return [
            // chow
            [['-1s', '2s', '3s'], '123s', '1s', 'prev'],
            [['-2s', '1s', '3s'], '123s', '2s', 'prev'],
            [['-3s', '1s', '2s'], '123s', '3s', 'prev'],
            // pung
            [['-0s', '5s', '5s'], '550s', '0s', 'prev'],
            [['5s', '-0s', '5s'], '550s', '0s', 'towards'],
            [['5s', '5s', '-0s'], '550s', '0s', 'next'],
            // kong
            [['-0s', '5s', '5s', '5s'], '5550s', '0s', 'prev'],
            [['5s', '-0s', '5s', '5s'], '5550s', '0s', 'towards'],
            [['5s', '5s', '5s', '-0s'], '5550s', '0s', 'next'],
        ];
    }

//    /**
//     * @daraProvide provideToJsonSelf
//     */
//    function testToJsonSelf() {
//    }
//
//    function provideToJsonSelf() {
//        return [
//            // extendKong
//            [[]],
//            // concealedKong
//        ];
//    }
}