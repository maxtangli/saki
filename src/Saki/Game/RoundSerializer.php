<?php
namespace Saki\Game;

use Saki\Util\Singleton;
use Saki\Util\Utils;
use Saki\Win\WinReport;

/**
 * @package Saki\Game
 */
class RoundSerializer extends Singleton {
    function toArray(Round $r) {
        $commandProvider = $r->getProcessor()->getProvider();

        $prevailing = $r->getPrevailing();
        $phase = $r->getPhase();
        $round = [
            'isGameOver' => $r->isGameOver(),
            'prevailing' => $prevailing->__toString(),
            'prevailingWind' => $prevailing->getStatus()->getPrevailingWind()->__toString(),
            'prevailingWindTurn' => $prevailing->getStatus()->getPrevailingWindTurn(),
            'seatWindTurn' => $prevailing->getSeatWindTurn(),
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

        $w = $r->getWall();
        $wall = [
            'remainTileCount' => $w->getRemainTileCount(),
            'doraIndicators' => $w->getDeadWall()->getOpenedDoraIndicatorList()->toArray(Utils::getToStringCallback()),
            'uraDoraIndicators' => $w->getDeadWall()->getOpenedUraDoraIndicatorList()->toArray(Utils::getToStringCallback()),
        ];

        $areas = [];
        /** @var Area $area */
        foreach ($r->getAreaList() as $area) {
            $hand = $area->getHand();
            $actor = $area->getSeatWind();

            $areas[] = [
                'actor' => $actor->__toString(),
                'point' => $area->getPoint(),
                'isReach' => $area->getRiichiStatus()->isRiichi(),
                'discard' => $area->getDiscard()->toArray(Utils::getToStringCallback()),
                'public' => $hand->getPublic()->toTileList()->orderByTileID()->toArray(Utils::getToStringCallback()),
                'target' => $hand->getTarget()->exist() ? $hand->getTarget()->getTile()->toFormatString(true) : null,
                'melded' => $hand->getMelded()->toTileStringArrayArray(),
                'commands' => $commandProvider->getExecutableList($actor)->toArray(Utils::getToStringCallback()),
            ];
        }

        $a = [
            'result' => 'ok',
            'round' => $round,
            'wall' => $wall,
            'areas' => $areas,
        ];

        return $a;
    }

    /**
     * @param Round $round
     * @return string
     */
    function toString(Round $round) {
        return implode("\n", $this->toArray($round));
    }
    
    /**
     * @param Round $round
     * @return string
     */
    function toJson(Round $round) {
        return json_encode($this->toArray($round));
    }
}