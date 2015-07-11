<?php
namespace Saki\Game;

class GameResult {

    /**
     * @var GameResultItem[]
     */
    private $resultItems;

    /**
     * @param Player[] $players
     */
    function __construct(array $players) {
        $rankedPlayers = $players;
        usort($rankedPlayers, function (Player $a, Player $b) {
            if ($a->getScore() == $b->getScore()) {
                return $a->getNo() < $b->getNo() ? 1 : -1;
            } else {
                return $a->getScore() > $b->getScore() ? 1 : -1;
            }
        });
        $rankedPlayers = array_values(array_reverse($rankedPlayers));

        $initialScore = 25000;
        $forCalculateInitialScore = 30000;
        $resultItems = [];
        foreach ($rankedPlayers as $k => $player) {
            $rank = $k + 1;
            $finalScore = $player->getScore();
            $scorePoint = $this->getTotalPoint($initialScore, $forCalculateInitialScore, $finalScore, $rank);
            $resultItem = new GameResultItem($rank, $finalScore, $scorePoint);

            $no = $player->getNo();
            $resultItems[$no] = $resultItem;
        }

        $this->resultItems = $resultItems;
    }

    function getResultItem(Player $player) {
        return $this->resultItems[$player->getNo()];
    }

    protected function getTotalPoint($initialScore, $forCalculateInitialScore, $finalScore, $rank) {
        $isTopRank = ($rank == 1);
        $totalPoint = $this->getUmaPoint($rank) + $this->getOkaPoint($initialScore, $forCalculateInitialScore, $finalScore, $isTopRank);
        return $totalPoint;
    }

    /**
     * 順位ウマ 10-20
     * @param $rank
     */
    protected function getUmaPoint($rank) {
        $a = [
            1 => 20,
            2 => 10,
            3 => -10,
            4 => -20,
        ];
        return $a[$rank];
    }

    /**
     * オカ 切り上げ
     * @param $initialScore 配給原点（はいきゅうげんてん） 半荘開始時の持ち点
     * @param $forCalculateInitialScore 原点（げんてん） 半荘終了時の成績評価に使う
     * @param $finalScore
     * @param $isTopRank
     * @return int
     */
    protected function getOkaPoint($initialScore, $forCalculateInitialScore, $finalScore, $isTopRank) {
        $playerCount = 4;
        $plusScore = $isTopRank ? $playerCount * ($forCalculateInitialScore - $initialScore) : 0;
        $actualFinalScore = $finalScore + $plusScore;

        $scoreDeltaInt = $actualFinalScore - $forCalculateInitialScore;
        $okaPoint = $scoreDeltaInt >= 0 ? ceil($scoreDeltaInt / 1000) : floor($scoreDeltaInt / 1000);
        return intval($okaPoint);
    }
}