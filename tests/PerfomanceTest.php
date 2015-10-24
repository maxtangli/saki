<?php

use Saki\Game\Round;
use Saki\Game\RoundData;
use Saki\Game\Wall;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSet;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;
use Saki\Util\Timer;
use Saki\Util\Utils;
use Saki\Win\WaitingAnalyzer;
use Saki\Win\WinAnalyzer;

class PerformanceTest extends \PHPUnit_Framework_TestCase {
    function testRound() {
        Timer::getInstance()->reset();
        new RoundData();
//        Timer::getInstance()->showAndReset();
    }
}