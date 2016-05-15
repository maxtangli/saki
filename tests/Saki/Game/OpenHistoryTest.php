<?php

use Saki\Game\OpenHistory;
use Saki\Game\OpenRecord;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Tile\Tile;

class OpenHistoryTest extends PHPUnit_Framework_TestCase {
    /** @var  OpenHistory */
    protected $h;

    protected function setUp() {
        $this->h = $this->h ?? new OpenHistory();
        $this->h->reset();
    }

    protected function record(string $turn, string $tile, bool $isDiscard = true) {
        $this->h->record(
            new OpenRecord(Turn::fromString($turn), Tile::fromString($tile), $isDiscard)
        );
    }

    protected function assertGetLastOpenOrFalse($expectedStringOrBool, string $seatWind) {
        $actual = $this->h->getLastOpenTurnOrFalse(SeatWind::fromString($seatWind));
        $expected = is_bool($expectedStringOrBool) ? $expectedStringOrBool : Turn::fromString($expectedStringOrBool);
        $this->assertEquals($expected, $actual);
    }

    protected function assertGetSelf(string $expectedTileList, string $seatWind) {
        $actual = $this->h->getSelfOpen(SeatWind::fromString($seatWind));
        $this->assertEquals($expectedTileList, $actual->__toString());
    }

    protected function assertGetOther(string $expectedTileList, string $seatWind, string $fromTurn) {
        $actual = $this->h->getOtherOpen(SeatWind::fromString($seatWind), Turn::fromString($fromTurn));
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
    }
}