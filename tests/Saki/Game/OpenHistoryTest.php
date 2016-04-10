<?php

use Saki\Game\OpenHistory;
use Saki\Game\OpenRecord;
use Saki\Game\PlayerWind;
use Saki\Game\RoundTurn;
use Saki\Tile\Tile;

class OpenHistoryTest extends PHPUnit_Framework_TestCase {
    /** @var  OpenHistory */
    protected $h;

    protected function setUp() {
        $this->h = $this->h ?? new OpenHistory();
        $this->h->reset();
    }

    protected function record(string $roundTurn, string $tile, bool $isDiscard = true) {
        $this->h->record(
            new OpenRecord(RoundTurn::fromString($roundTurn), Tile::fromString($tile), $isDiscard)
        );
    }

    protected function assertGetLastOpenOrFalse($expectedStringOrBool, string $playerWind) {
        $actual = $this->h->getLastOpenOrFalse(PlayerWind::fromString($playerWind));
        $expected = is_bool($expectedStringOrBool) ? $expectedStringOrBool : RoundTurn::fromString($expectedStringOrBool);
        $this->assertEquals($expected, $actual);
    }

    protected function assertGetSelf(string $expectedTileList, string $playerWind) {
        $actual = $this->h->getSelf(PlayerWind::fromString($playerWind));
        $this->assertEquals($expectedTileList, $actual->__toString());
    }

    protected function assertGetOther(string $expectedTileList, string $playerWind, string $fromRoundTurn) {
        $actual = $this->h->getOther(PlayerWind::fromString($playerWind), RoundTurn::fromString($fromRoundTurn));
        $this->assertEquals($expectedTileList, $actual->__toString());
    }

    protected function assertGetAllDiscard(string $expectedTileList) {
        $actual = $this->h->getAllDiscard();
        $this->assertEquals($expectedTileList, $actual->__toString());
    }

    function testAll() {
        $this->record('1E', '1m');
        $this->record('1S', '2m');
        $this->record('1W', '3m');
        $this->assertGetLastOpenOrFalse(false, 'N');
        $this->record('1N', '4m');
        $this->assertGetLastOpenOrFalse('1N', 'N');
        $this->record('3E', '5m', false);
        $this->record('3S', '6m');
        $this->record('3W', '7m');
        $this->assertGetLastOpenOrFalse('1N', 'N');
        $this->record('3N', '8m');
        $this->assertGetLastOpenOrFalse('3N', 'N');

        $this->assertGetSelf('15m', 'E');
        $this->assertGetOther('3467m', 'E', '1W');
        $this->assertGetAllDiscard('1234678m');
    }
}