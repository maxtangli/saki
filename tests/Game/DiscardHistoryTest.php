<?php

use Saki\Game\DiscardHistoryItem;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class DiscardHistoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider itemProvider
     */
    function testItem(DiscardHistoryItem $later, DiscardHistoryItem $before, $expected, $allowSameTurnAndSelfWind = false) {
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
            [new DiscardHistoryItem(1, $e, $notUsed), new DiscardHistoryItem(1, $e, $notUsed), false],
            [new DiscardHistoryItem(1, $s, $notUsed), new DiscardHistoryItem(1, $e, $notUsed), true],
            [new DiscardHistoryItem(1, $e, $notUsed), new DiscardHistoryItem(1, $n, $notUsed), false],
            // diff turn
            [new DiscardHistoryItem(2, $e, $notUsed), new DiscardHistoryItem(1, $e, $notUsed), true],
            [new DiscardHistoryItem(2, $s, $notUsed), new DiscardHistoryItem(1, $e, $notUsed), true],
            [new DiscardHistoryItem(2, $e, $notUsed), new DiscardHistoryItem(1, $n, $notUsed), true],
        ];
    }

    /**
     * @depends testItem
     */
    function testHistory() {
        $h = new \Saki\Game\DiscardHistory();
        $this->assertEquals(TileList::fromString(''), $h->getSelfDiscardTileList(Tile::fromString('E')));
        $this->assertEquals(TileList::fromString(''), $h->getOtherDiscardTileList(Tile::fromString('E')));

        $h->recordDiscardTile(1, Tile::fromString('E'), Tile::fromString('1s'));
        $this->assertEquals(TileList::fromString('1s'), $h->getSelfDiscardTileList(Tile::fromString('E')));
        $this->assertEquals(TileList::fromString(''), $h->getOtherDiscardTileList(Tile::fromString('E')));

        $h->recordDiscardTile(1, Tile::fromString('S'), Tile::fromString('2s'));
        $this->assertEquals(TileList::fromString('1s'), $h->getSelfDiscardTileList(Tile::fromString('E')));
        $this->assertEquals(TileList::fromString('2s'), $h->getOtherDiscardTileList(Tile::fromString('E')));

        $h->recordDiscardTile(2, Tile::fromString('E'), Tile::fromString('3s'));
        $h->recordDiscardTile(2, Tile::fromString('S'), Tile::fromString('4s'));
        $h->recordDiscardTile(2, Tile::fromString('W'), Tile::fromString('5s'));
        $h->recordDiscardTile(2, Tile::fromString('N'), Tile::fromString('6s'));

        $h->recordDiscardTile(3, Tile::fromString('E'), Tile::fromString('7s'));
        $h->recordDiscardTile(3, Tile::fromString('S'), Tile::fromString('8s'));
        $h->recordDiscardTile(3, Tile::fromString('W'), Tile::fromString('9s'));

        $this->assertEquals(TileList::fromString('45689s'), $h->getOtherDiscardTileList(Tile::fromString('E'), 2, Tile::fromString('E')));
        $this->assertEquals(TileList::fromString('5679s'), $h->getOtherDiscardTileList(Tile::fromString('S'), 2, Tile::fromString('S')));
        $this->assertEquals(TileList::fromString('678s'), $h->getOtherDiscardTileList(Tile::fromString('W'), 2, Tile::fromString('W')));
        $this->assertEquals(TileList::fromString('789s'), $h->getOtherDiscardTileList(Tile::fromString('N'), 2, Tile::fromString('N')));
    }
}
