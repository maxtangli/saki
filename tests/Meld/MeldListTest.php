<?php

use Saki\Meld\Meld;
use Saki\Meld\MeldList;

class MeldListTest extends PHPUnit_Framework_TestCase {
    function testOverall() {

        $l = new MeldList([
            new Meld(\Saki\Tile\TileList::fromString('11m'), \Saki\Meld\PairMeldType::getInstance())
        ]);
    }

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
}
