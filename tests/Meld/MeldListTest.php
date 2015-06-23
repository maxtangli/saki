<?php

use Saki\Meld\MeldList;
use Saki\Meld\Meld;
class MeldListTest extends PHPUnit_Framework_TestCase {
    function testOverall() {

        $l = new MeldList([
            new Meld(\Saki\TileList::fromString('11m'), \Saki\Meld\PairMeldType::getInstance())
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
            [',',false],
            [',11s',false]
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
}
