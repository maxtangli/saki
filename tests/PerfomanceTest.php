<?php

use Saki\Game\GameData;
use Saki\Game\MockRound;
use Saki\Game\PlayerList;
use Saki\Game\Round;
use Saki\Game\RoundData;
use Saki\Game\RoundWindData;
use Saki\Game\TileAreas;
use Saki\Game\TurnManager;
use Saki\Game\Wall;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\Benchmark;
use Saki\Util\BenchmarkItem;

class PerformanceTest extends PHPUnit_Framework_TestCase {
    function testNot() {

    }

    function notTestBenchmark() {
//    function testBenchmark() {
        $a = [
            $this->getTileAreaBenchmark(),
            $this->getRoundBenchmark(),
            $this->getRoundDataBenchmark(),
        ];

        $s = implode("\n", $a);

        echo $s;
        $this->writeLog($s);
    }

    protected function writeLog($s) {
        $file = __DIR__.'/PerformanceTestResult.md';
        $now = new DateTime();
        $content = sprintf("%s\n\n%s", $now->format(DateTime::ISO8601), $s);
        file_put_contents($file, $content);
    }

    protected function getTileAreaBenchmark() {
        $b = new Benchmark('TileArea');

        $r = new Round();
        $tileAreas = $r->getRoundData()->getTileAreas();
        $currentPlayer = $r->getCurrentPlayer();
        $b->add(new BenchmarkItem('get13styleHandTileList()', function() use($tileAreas, $currentPlayer) {
            return $tileAreas->getPublicHand($currentPlayer);
        }));
        $b->add(new BenchmarkItem('get14styleHandTileList()', function() use($tileAreas, $currentPlayer) {
            return $tileAreas->getPrivateHand($currentPlayer);
        }));
        return $b;
    }

    protected function getRoundBenchmark() {
        $b = new Benchmark('Round');

        $b->add(new BenchmarkItem('new Round()', function() {
            return new Round();
        }));

        $r = new MockRound();
        $tile = $r->getRoundData()->getTileAreas()->getPrivateHand($r->getCurrentPlayer());
        $b->add(new BenchmarkItem('discard()', function()use($r, $tile) {
            $r->discard($r->getCurrentPlayer(), $tile);
        }));

        $b->add(new BenchmarkItem('passPublicPhase()', function()use($r) {
            $r->passPublicPhase();
        }));

        $b->add(new BenchmarkItem('debugDiscardByReplace()', function()use($r) {
            $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        }));

        $notWinPlayer = $r->getPlayerList()->getSelfWindPlayer(Tile::fromString('S'));
        $r->debugSetHand($notWinPlayer, TileList::fromString('13579m13579p135s'));
        $b->add(new BenchmarkItem('getWinResult()//notWin', function () use ($r, $notWinPlayer) {
            return $r->getWinResult($notWinPlayer);
        }));

        $winPlayer = $r->getPlayerList()->getSelfWindPlayer(Tile::fromString('W'));
        $r->debugSetHand($winPlayer, TileList::fromString('123456789m123sE'));
        $b->add(new BenchmarkItem('getWinResult()//win', function () use ($r, $winPlayer) {
            return $r->getWinResult($winPlayer);
        }));

        return $b;
    }

    protected function getRoundDataBenchmark() {
        $b = new Benchmark('RoundData');
        $b->add(new BenchmarkItem('new RoundData()', function() {
            return new RoundData();
        }));

        $roundData = new RoundData();
        $b->add(new BenchmarkItem('RoundData.reset(false)', function() use($roundData) {
            return $roundData->reset(false);
        }));

        $b->add(new BenchmarkItem('new GameData()', function() {
            return new GameData();
        }));

        $gameData = $roundData->getGameData();
        $b->add(new BenchmarkItem('new RoundWindData(...)', function() use($gameData) {
            return new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());
        }));
        $b->add(new BenchmarkItem('new PlayerList(...)', function () use ($gameData) {
            return new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        }));

        $playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        $b->add(new BenchmarkItem('new TurnManager(...)', function () use ($playerList) {
            return new TurnManager($playerList);
        }));

        $b->add(new BenchmarkItem('new Wall(...)', function () use ($gameData) {
            return new Wall($gameData->getTileSet());
        }));

        $wall = $roundData->getTileAreas()->getWall();
        $turnManager = $roundData->getTurnManager();
        $b->add(new BenchmarkItem('new TileAreas(...)', function() use($wall, $playerList, $turnManager) {
            new TileAreas($wall, $playerList, function () use($turnManager) {
                return $turnManager->getRoundTurn();
            });
        }));
        return $b;
    }
}

