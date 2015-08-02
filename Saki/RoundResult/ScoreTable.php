<?php
namespace Saki\RoundResult;

use Saki\Util\Singleton;

class ScoreTable extends Singleton {
    private $fuCounts = [20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 110];
    private $fanCounts = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

    function getFuCounts() {
        return $this->fuCounts;
    }

    function getFanCounts() {
        return $this->fanCounts;
    }

    function getScoreItem($fanCount, $fuCount = null) {
        $scoreLevel = ScoreLevel::fromFanAndFuCount($fanCount, $fuCount);
        if ($scoreLevel == ScoreLevel::getInstance(ScoreLevel::NONE)) {
            $baseScore = $fuCount * intval(pow(2, $fanCount + 2));
        } elseif (!$scoreLevel->isYakuMan()) {
            $m = [
                ScoreLevel::MAN_GAN => 2000,
                ScoreLevel::HANE_MAN => 3000,
                ScoreLevel::BAI_MAN => 4000,
                ScoreLevel::SAN_BAI_MAN => 6000,
            ];
            $baseScore = $m[$scoreLevel->getValue()];
        } else {
            $yakuManCount = intval($fanCount / 13);
            $baseScore = 8000 * $yakuManCount;
        }
        return new ScoreTableItem($baseScore);
    }

    /**
     * @return ScoreTable
     */
    static function getInstance() {
        return parent::getInstance();
    }
}