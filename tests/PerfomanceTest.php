<?php

use Saki\Game\GameData;
use Saki\Game\PlayerList;
use Saki\Game\Round;
use Saki\Game\RoundWindData;
use Saki\Game\TileAreas;
use Saki\Game\TurnManager;
use Saki\Game\Wall;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Util\Benchmark;
use Saki\Util\BenchmarkItem;
use Saki\Win\Fu\FuCountAnalyzer;
use Saki\Win\Fu\FuCountTarget;
use Saki\Win\WinTarget;
use Saki\Win\Yaku\Yaku;

class PerformanceTest extends PHPUnit_Framework_TestCase {
    function testNot() {

    }

//    function notTestBenchmark() {
    function testBenchmark() {
        $a = [
//            $this->getRoundDataBenchmark(),
//            $this->getRoundBenchmark(),
//            $this->getWinAnalyzerBenchmark(),
//            $this->getWaitingAnalyzerBenchmark(),
//            $this->getYakuAnalyzerBenchmark(),
//            $this->getTileSortedListBenchmark(),
        ];

        $s = implode("\n", $a);

        echo $s;
        $this->writeLog($s);
    }

    protected function writeLog($s) {
        $file = __DIR__ . '/PerformanceTestResult.md';
        $now = new DateTime();
        $content = sprintf("%s\n\n%s", $now->format(DateTime::ISO8601), $s);
        file_put_contents($file, $content);
    }

    protected function getRoundBenchmark() {
        $b = new Benchmark('Round');

        $b->add(new BenchmarkItem('new Round()', function () {
            return new Round();
        }));
        return $b;
    }

    protected function getRoundDataBenchmark() {
        $b = new Benchmark('Round');
        $b->add(new BenchmarkItem('new Round()', function () {
            return new Round();
        }));
        return $b;
    }

    protected function getWinAnalyzerBenchmark() {
        $b = new Benchmark('WinAnalyzer');

        $r = new Round();
        $r->getTileAreas()->debugSetPrivate($r->getTurnManager()->getCurrentPlayer(),
            TileSortedList::fromString('123456789m234sWW'), null, Tile::fromString('2s'));

        $b->add(new BenchmarkItem('Round.getWinResult()', function () use ($r) {
            $r->getWinResult($r->getTurnManager()->getCurrentPlayer());
        }));

        $analyzer = $r->getWinAnalyzer();
        $target = new WinTarget($r->getTurnManager()->getCurrentPlayer(), $r);
        $b->add(new BenchmarkItem('analyzeTarget()', function () use ($analyzer, $target) {
            $analyzer->analyzeTarget($target);
        }));

        $handMeldList = MeldList::fromString('(123m),(456m),(789m),(234s),(WW)');
        $subTarget = $target->toSubTarget($handMeldList);
        $b->add(new BenchmarkItem('analyzeSubTarget()', function () use ($analyzer, $subTarget) {
            $analyzer->analyzeSubTarget($subTarget);
        }));

        $b->add(new BenchmarkItem('analyzeWaitingTileList()', function () use ($analyzer, $target) {
            $publicHandTileList = $target->getPublicHand();
            $waitingTileList = $analyzer->getWaitingAnalyzer()->analyzePublic(
                $publicHandTileList, $target->getDeclaredMeldList()
            );
            $analyzer->isFuritenFalseWin($target, $waitingTileList);
        }));

        $b->add(new BenchmarkItem('analyzeSubTarget().analyzeTileSeries()', function () use ($analyzer, $subTarget) {
            $tileSeries = $analyzer->getTileSeriesAnalyzer()->analyzeTileSeries($subTarget->getAllMeldList());
        }));

        $b->add(new BenchmarkItem('analyzeSubTarget().analyzeYakuList()', function () use ($analyzer, $subTarget) {
            $yakuList = $analyzer->getYakuAnalyzer()->analyzeYakuList($subTarget);
        }));

        $tileSeries = $analyzer->getTileSeriesAnalyzer()->analyzeTileSeries($subTarget->getAllMeldList());
        $b->add(new BenchmarkItem('analyzeSubTarget().getWaitingType()', function () use ($analyzer, $subTarget, $tileSeries) {
            $waitingType = $tileSeries->getWaitingType($subTarget->getAllMeldList(), $subTarget->getTileOfTargetTile(), $subTarget->getDeclaredMeldList());
        }));

        $yakuList = $analyzer->getYakuAnalyzer()->analyzeYakuList($subTarget);
        $waitingType = $tileSeries->getWaitingType($subTarget->getAllMeldList(), $subTarget->getTileOfTargetTile(), $subTarget->getDeclaredMeldList());
        $b->add(new BenchmarkItem('analyzeSubTarget().getFuCount()', function () use ($analyzer, $subTarget, $yakuList, $waitingType) {
            $fuCountTarget = new FuCountTarget($subTarget, $yakuList, $waitingType);
            $fuCountResult = FuCountAnalyzer::getInstance()->getResult($fuCountTarget);
            $fuCount = $fuCountResult->getTotalFuCount();
        }));

        return $b;
    }

    protected function getWaitingAnalyzerBenchmark() {
        $b = new Benchmark('WaitingAnalyzer');

        $r = new Round();
        $r->getTileAreas()->debugSetPrivate($r->getTurnManager()->getCurrentPlayer(),
            TileSortedList::fromString('123456789m234sWW'), null, Tile::fromString('2s'));
        $analyzer = $r->getWinAnalyzer();
        $target = new WinTarget($r->getTurnManager()->getCurrentPlayer(), $r);
        $waitingAnalyzer = $analyzer->getWaitingAnalyzer();

        $b->add(new BenchmarkItem('analyzeMeldCompositions', function () use ($waitingAnalyzer, $target) {
            $meldTypes = [
                RunMeldType::getInstance(), TripleMeldType::getInstance(),
                PairMeldType::getInstance(),
                WeakRunMeldType::getInstance(), WeakPairMeldType::getInstance(),
            ];
            $handMeldLists = $waitingAnalyzer->getMeldCompositionsAnalyzer()
                ->analyzeMeldCompositions($target->getPublicHand(), $meldTypes, 1); // 10ms
        }));

        $b->add(new BenchmarkItem('analyzePublic', function () use ($target, $waitingAnalyzer) {
            $waitingAnalyzer->analyzePublic($target->getPublicHand(), $target->getDeclaredMeldList());
        }));

        $b->add(new BenchmarkItem('analyzePrivate', function () use ($target, $waitingAnalyzer) {
            $waitingAnalyzer->analyzePrivate($target->getPrivateHand(), $target->getDeclaredMeldList());
        }));

        return $b;
    }

    protected function getYakuAnalyzerBenchmark() {
        $b = new Benchmark('YakuAnalyzer');

        $r = new Round();
        $r->getTileAreas()->debugSetPrivate($r->getTurnManager()->getCurrentPlayer(),
            TileSortedList::fromString('123456789m234sWW'), null, Tile::fromString('2s'));
        $analyzer = $r->getWinAnalyzer();
        $target = new WinTarget($r->getTurnManager()->getCurrentPlayer(), $r);
        $handMeldList = MeldList::fromString('(123m),(456m),(789m),(234s),(WW)');
        $subTarget = $target->toSubTarget($handMeldList);
        $yakuAnalyzer = $analyzer->getYakuAnalyzer();

        $b->add(new BenchmarkItem('YakuAnalyzer.analyzeYakuList()', function () use ($yakuAnalyzer, $subTarget) {
            $yakuList = $yakuAnalyzer->analyzeYakuList($subTarget);
        }));

        foreach ($yakuAnalyzer->getYakuSet() as $yaku) {
            /** @var Yaku $yaku */
            $yaku = $yaku;
            $b->add(new BenchmarkItem($yaku->__toString() . '.existIn()', function () use ($yaku, $subTarget) {
                $yaku->existIn($subTarget);
            }));
        }

        $meldList = MeldList::fromString('(123m),(456m),(789m),(234s),(WW)');
        $b->add(new BenchmarkItem('meldList.isFourRun()*100', function () use ($meldList) {
            for ($i = 0; $i < 100; ++$i) {
                $meldList->isFourRunAndOnePair();
            }
        }));

        return $b;
    }

    protected function getTileSortedListBenchmark() {
        $b = new Benchmark('TileSortedList');

        $a = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 0, 0];
        $b->add(new BenchmarkItem('sort(array)', function () use ($a) {
            sort($a);
        })); // 0.0ms

        $tiles = TileList::fromString('123456789m123sWW')->toArray();
        $b->add(new BenchmarkItem('TileList::fromString(\'123456789m123sWW\')', function () use ($tiles) {
            return TileList::fromString('123456789m123sWW');
        })); // 0.1ms

        $b->add(new BenchmarkItem('TileSortedList::fromString(\'123456789m123sWW\')', function () use ($tiles) {
            return TileSortedList::fromString('123456789m123sWW');
        })); // 0.9ms -> 0.5ms -> 0.3ms

        $b->add(new BenchmarkItem('TileSortedList::fromString(\'123456789m123sWW\')', function () use ($tiles) {
            return TileSortedList::fromString('123456789m123sWW');
        })); // 0.9ms -> 0.5ms -> 0.3ms

        return $b;
    }
}