<?php
namespace Saki\Game;

use Saki\Util\Singleton;
use Saki\Util\Utils;
use Saki\Win\WinReport;

/**
 * @package Saki\Game
 */
class RoundSerializer extends Singleton {
    /**
     * @param Round $r
     * @param SeatWind|null $viewer
     * @return array
     */
    function toJson(Round $r, SeatWind $viewer = null) {
        $commandProvider = $r->getProcessor()->getProvider();

        $prevailing = $r->getPrevailing();
        $phase = $r->getPhase();
        $round = [
            'isGameOver' => $r->isGameOver(),
            'prevailingWind' => $prevailing->getStatus()->getPrevailingWind()->__toString(),
            'prevailingWindTurn' => $prevailing->getStatus()->getPrevailingWindTurn(),
            'seatWindTurn' => $prevailing->getSeatWindTurn(),
            'pointSticks' => $r->getRiichiHolder()->getRiichiPointsSticks(),
            'wall' => $r->getWall()->toJson(),
            'phase' => $phase->__toString(),
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
        } else {
            $round['result'] = null;
            $round['winReports'] = [];
        }

        $toArea = function (Area $area) use($viewer, $commandProvider) {
            $a = $area->toJson($viewer);
            $a['commands'] = $commandProvider->getExecutableList($area->getSeatWind())
                ->toArray(Utils::getToStringCallback());
            return $a;
        };
        $toRelation = function (Area $area) use($viewer) {
            return $area->getSeatWind()->toRelation($viewer);
        };
        $areas = $r->getAreaList()->toArray($toArea, $toRelation);

        $a = [
            'result' => 'ok',
            'round' => $round,
            'areas' => $areas,
        ];

        return $a;
    }

    /**
     * @param Round $round
     * @param SeatWind $viewer
     * @return string
     */
    function toString(Round $round, SeatWind $viewer = null) {
        return implode("\n", $this->toJson($round, $viewer));
    }
}