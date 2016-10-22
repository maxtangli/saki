<?php
namespace Saki\Game;

use Saki\Util\Singleton;
use Saki\Util\Utils;
use Saki\Win\WinReport;

/**
 * @package Saki\Game
 */
class RoundSerializer extends Singleton {
    function toJsonArray(Round $r) {
        $commandProvider = $r->getProcessor()->getProvider();

        $prevailing = $r->getPrevailing();
        $phase = $r->getPhase();
        $round = [
            'isGameOver' => $r->isGameOver(),
            'prevailingWind' => $prevailing->getStatus()->getPrevailingWind()->__toString(),
            'prevailingWindTurn' => $prevailing->getStatus()->getPrevailingWindTurn(),
            'seatWindTurn' => $prevailing->getSeatWindTurn(),
            'pointSticks' => $r->getRiichiHolder()->getRiichiPointsSticks(),
            'wall' => $r->getWall()->toJsonArray(),
            'phase' => $phase->__toString(),
            'result' => null,
            'winReports' => [],
        ];

        if ($phase->isOver()) {
            $result = $r->getPhaseState()->getResult();
            $round['result'] = $result->__toString();
            if ($result->getResultType()->isWin()) {
                $toWinReportJson = function (WinReport $winReport) {
                    return [
                        'actor' => $winReport->getActor()->__toString(),
                        'fan' => $winReport->getFan(),
                        'fu' => $winReport->getFu(),
                        'yakuItems' => $winReport->getYakuItemList()->toArray(Utils::getToStringCallback()),
                    ];
                };
                $round['winReports'] = $result->getWinReportList()->toArray($toWinReportJson);
            }
        }

        $toAreaJsonArray = function (Area $area) use($commandProvider) {
            $a = $area->toJsonArray();
            $a['commands'] = $commandProvider->getExecutableList($area->getSeatWind())
                ->toArray(Utils::getToStringCallback());
            return $a;
        };
        $areas = $r->getAreaList()->toArray($toAreaJsonArray);

        $a = [
            'result' => 'ok',
            'round' => $round,
            'areas' => $areas,
        ];

        return $a;
    }

    /**
     * @param Round $round
     * @return string
     */
    function toString(Round $round) {
        return implode("\n", $this->toJsonArray($round));
    }
    
    /**
     * @param Round $round
     * @return string
     */
    function toJson(Round $round) {
        return json_encode($this->toJsonArray($round));
    }
}