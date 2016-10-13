<?php

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Util\ArrayList;

class MeldListTest extends \SakiTestCase {
    /**
     * @dataProvider validStringProvider
     */
    function testValidString($s, $expected) {
        if ($expected) {
            $this->assertTrue(MeldList::validString($s), "\$s[$s]");
        } else {
            $this->assertFalse(MeldList::validString($s), "\$s[$s]");
        }
    }

    function validStringProvider() {
        return [
            ['', true],
            ['11s', true],
            ['11s,11s', true],
            [',', false],
            [',11s', false]
        ];
    }

    /**
     * @dataProvider validStringProvider
     */
    function testFromString($s, $valid) {
        if (!$valid) {
            return;
        }
        $this->assertSame($s, MeldList::fromString($s)->__toString(), "\$s[$s]");
    }

    function testTileExist() {
        $meldList = MeldList::fromString('123m,123s,EE');
        $this->assertTrue($meldList->tileExist(\Saki\Tile\Tile::fromString('2s')));
        $this->assertFalse($meldList->tileExist(\Saki\Tile\Tile::fromString('4s')));
    }

    function testSeries() {
        $this->assertTrue(MeldList::fromString('123s,456s,789s,111s,11s')->isFourWinSetAndOnePair());
    }

    function testOutsideHand() {
        $this->assertTrue(Meld::fromString('123m')->isAnyTermOrHonour());
        $this->assertTrue(Meld::fromString('789s')->isAnyTermOrHonour());
        $this->assertTrue(Meld::fromString('EE')->isAnyTermOrHonour());
        $this->assertTrue(Meld::fromString('EEE')->isAnyTermOrHonour());
        $this->assertTrue(Meld::fromString('EEEE')->isAnyTermOrHonour());
        $this->assertTrue(MeldList::fromString('123m,789m,123s,789s,EE')->isOutsideHand(false));
    }

    function testLoop() {
        $a = new ArrayList([
            [Meld::fromString('123m'), Meld::fromString('456m'), Meld::fromString('789m')],
            [Meld::fromString('123p'), Meld::fromString('456p'), Meld::fromString('789p')],
            [Meld::fromString('123s'), Meld::fromString('456s'), Meld::fromString('789s')],
        ]);
        $this->assertEquals(3, $a->count());
        foreach ($a as $v) {
            $this->assertEquals('array', gettype($v));
            $this->assertCount(3, $v);
        }
    }

    function testFullStraight() {
        $meldList = MeldList::fromString('123m,456m,789m,111m,EE');

        $this->assertTrue($meldList->valueExist(Meld::fromString('123m')));
        $this->assertTrue($meldList->valueExist(Meld::fromString('123m'), Meld::getCompareKeySelector(false)));

        $this->assertTrue($meldList->valueExist([Meld::fromString('123m'), Meld::fromString('456m')], Meld::getCompareKeySelector(false)));
        $this->assertTrue($meldList->valueExist([Meld::fromString('123m'), Meld::fromString('456m'), Meld::fromString('789m')], Meld::getCompareKeySelector(false)));

        $this->assertTrue($meldList->isPureStraight());

        // different isConcealed case

        $meldList = MeldList::fromString('(123m),456m,789m,111m,EE');

        $this->assertFalse($meldList->valueExist(Meld::fromString('123m')));
        $this->assertTrue($meldList->valueExist(Meld::fromString('123m'), Meld::getCompareKeySelector(false)));

        $this->assertTrue($meldList->valueExist([Meld::fromString('123m'), Meld::fromString('456m')], Meld::getCompareKeySelector(false)));
        $this->assertTrue($meldList->valueExist([Meld::fromString('123m'), Meld::fromString('456m'), Meld::fromString('789m')], Meld::getCompareKeySelector(false)));

        $this->assertTrue($meldList->isPureStraight());
    }
}
