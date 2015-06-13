<?php

use Saki\Tile;
use Saki\TileSortedList;

class PlayerAreaTest extends PHPUnit_Framework_TestCase {

    function testOverall() {
        $h = new \Saki\Game\PlayerArea(\Saki\TileSortedList::fromString('123m123p123sEEECC', false));

        /*
        $h->discardCandidate(\Saki\Tile::fromString('1m'));
        $this->assertCount(1, $h->getDiscardedTileList());

        $h->replace(Tile::fromString('1s'), Tile::fromString('4s'));
        $this->assertEquals('123m123p234sEEECC', $h->getOnHandTileSortedList()->__toString());
        $this->assertCount(2, $h->getDiscardedTileList());

        $h->chow(Tile::fromString('2m'), Tile::fromString('3m'), Tile::fromString('1m'));
        $this->assertEquals('1m123p234sEEECC', $h->getOnHandTileSortedList()->__toString());
        $this->assertCount(1, $h->getExposedMeldList());

        $h->exposedPong(Tile::fromString('C'), Tile::fromString('C'), Tile::fromString('C'));
        $this->assertEquals('1m123p234sEEE', $h->getOnHandTileSortedList()->__toString());
        $this->assertCount(2, $h->getExposedMeldList());

        $h->exposedKong(Tile::fromString('E'),Tile::fromString('E'),Tile::fromString('E'),Tile::fromString('E'));
        $this->assertEquals('1m123p234s', $h->getOnHandTileSortedList()->__toString());
        $this->assertCount(3, $h->getExposedMeldList());
        */
        // concealedPong
        // concealedKong
    }
}
