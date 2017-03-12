<?php

namespace Saki\Win\Draw;

use Saki\Game\Round;
use Saki\Util\ArrayList;

/**
 * @package Saki\Win\Draw
 */
class DrawAnalyzer {
    private static $standardInstance;

    static function createStandard() {
        self::$standardInstance = self::$standardInstance ?? new self([
                FourWindDraw::create(),
                FourRiichiDraw::create(),
                FourKongDraw::create(),
                NagashiManganDraw::create(),
                ExhaustiveDraw::create(), // low priority than FourRiichiDraw, FourKongDraw, NagashiManganDraw
            ]);
        return self::$standardInstance;
    }

    private $drawList;

    /**
     * @param Draw[] $drawSet A set of Draw used in a game where lower index means higher priority.
     */
    function __construct(array $drawSet) {
        $this->drawList = new ArrayList($drawSet);
    }

    /**
     * @return ArrayList An ArrayList of Draw used in a game.
     */
    function getDrawList() {
        return $this->drawList->getCopy();
    }

    /**
     * @param Round $round
     * @return Draw|false
     */
    function analyzeDrawOrFalse(Round $round) {
        return $this->getDrawList()->getFirstOrDefault(function (Draw $draw) use ($round) {
            return $draw->isDraw($round);
        }, false);
    }
}

