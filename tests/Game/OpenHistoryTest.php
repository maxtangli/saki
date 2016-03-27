<?php

use Saki\Game\OpenHistory;
use Saki\Game\OpenHistoryItem;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class OpenHistoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider itemProvider
     */
    function testItem(OpenHistoryItem $later, OpenHistoryItem $before, $expected, $allowSameTurnAndSelfWind = false) {
        $result = $later->validLaterItemOf($before, $allowSameTurnAndSelfWind);
        $this->assertEquals($expected, $result,
            sprintf('[%s]->validLaterItemOf([%s], [%s]) expected[%s] but actual[%s]', $later, $before,
                var_export($allowSameTurnAndSelfWind, true),
                var_export($expected, true),
                var_export($result, true))
        );
    }

    function itemProvider() {
        list($e, $s, $w, $n) = Tile::getWindTiles();
        $notUsed = $e;
        return [
            // same turn
            [new OpenHistoryItem(1, $e, $notUsed), new OpenHistoryItem(1, $e, $notUsed), false],
            [new OpenHistoryItem(1, $s, $notUsed), new OpenHistoryItem(1, $e, $notUsed), true],
            [new OpenHistoryItem(1, $e, $notUsed), new OpenHistoryItem(1, $n, $notUsed), false],
            // diff turn
            [new OpenHistoryItem(2, $e, $notUsed), new OpenHistoryItem(1, $e, $notUsed), true],
            [new OpenHistoryItem(2, $s, $notUsed), new OpenHistoryItem(1, $e, $notUsed), true],
            [new OpenHistoryItem(2, $e, $notUsed), new OpenHistoryItem(1, $n, $notUsed), true],
        ];
    }

    /**
     * @depends testItem
     */
    function testHistory() {
        $h = new OpenHistory();
        $this->assertEquals(TileList::fromString(''), $h->getSelf(Tile::fromString('E')));
        $this->assertEquals(TileList::fromString(''), $h->getOther(Tile::fromString('E')));

        $h->record(1, Tile::fromString('E'), Tile::fromString('1s'));
        $this->assertEquals(TileList::fromString('1s'), $h->getSelf(Tile::fromString('E')));
        $this->assertEquals(TileList::fromString(''), $h->getOther(Tile::fromString('E')));

        $h->record(1, Tile::fromString('S'), Tile::fromString('2s'));
        $this->assertEquals(TileList::fromString('1s'), $h->getSelf(Tile::fromString('E')));
        $this->assertEquals(TileList::fromString('2s'), $h->getOther(Tile::fromString('E')));

        $h->record(2, Tile::fromString('E'), Tile::fromString('3s'));
        $h->record(2, Tile::fromString('S'), Tile::fromString('4s'));
        $h->record(2, Tile::fromString('W'), Tile::fromString('5s'));
        $h->record(2, Tile::fromString('N'), Tile::fromString('6s'));

        $h->record(3, Tile::fromString('E'), Tile::fromString('7s'));
        $h->record(3, Tile::fromString('S'), Tile::fromString('8s'));
        $h->record(3, Tile::fromString('W'), Tile::fromString('9s'));

        $this->assertEquals(TileList::fromString('45689s'), $h->getOther(Tile::fromString('E'), 2, Tile::fromString('E')));
        $this->assertEquals(TileList::fromString('5679s'), $h->getOther(Tile::fromString('S'), 2, Tile::fromString('S')));
        $this->assertEquals(TileList::fromString('678s'), $h->getOther(Tile::fromString('W'), 2, Tile::fromString('W')));
        $this->assertEquals(TileList::fromString('789s'), $h->getOther(Tile::fromString('N'), 2, Tile::fromString('N')));
    }
}
