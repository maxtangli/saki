<?php
namespace Saki\Play;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Util\Utils;
use Saki\Win\WinReport;

/**
 * @package Saki\Play
 */
class RoundSerializer {
    private $round;
    private $role;

    /**
     * @param Round $round
     * @param Role $role
     */
    function __construct(Round $round, Role $role) {
        $this->round = $round;
        $this->role = $role;
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @return Role
     */
    function getRole() {
        return $this->role;
    }

    /**
     * @return array
     */
    function toAllJson() {
        $a = [
            'response' => 'ok',
            'round' => $this->toRoundJson(),
            'areas' => $this->toAreasJson(),
            'result' => $this->toResultJson(),
        ];
        return $a;
    }

    /**
     * @return array
     */
    function toRoundJson() {
        $round = $this->getRound();
        $prevailing = $round->getPrevailing();
        $a = [
            'isGameOver' => $round->isGameOver(),
            'prevailingWind' => $prevailing->getStatus()->getPrevailingWind()->__toString(),
            'prevailingWindTurn' => $prevailing->getStatus()->getPrevailingWindTurn(),
            'seatWindTurn' => $prevailing->getSeatWindTurn(),
            'pointSticks' => $round->getRiichiHolder()->getRiichiPointsSticks(),
            'wall' => $round->getWall()->toJson(),
            'phase' => $round->getPhase()->__toString(),
        ];
        return $a;
    }

    /**
     * @return array ['self' => $selfAreaJson, 'next' => $nextAreaJson ...]
     */
    function toAreasJson() {
        $areaList = $this->getRound()->getAreaList();
        $toRelation = function (Area $area) {
            return $this->getRole()->getRelation($area->getSeatWind());
        };
        return $areaList->toArray([$this, 'toAreaJson'], $toRelation);
    }

    /**
     * @param Area $area
     * @return array
     */
    function toAreaJson(Area $area) {
        $actor = $area->getSeatWind();
        $hand = $area->getHand();
        $commandProvider = $this->getRound()->getProcessor()->getProvider();
        $a = [
            'relation' => $this->getRole()->getRelation($actor),
            'actor' => $actor->__toString(),
            'point' => $area->getPoint(),
            'isReach' => $area->getRiichiStatus()->isRiichi(),
            'discard' => $area->getDiscard()->toArray(Utils::getToStringCallback()),
            'public' => $hand->getPublic()->toTileList()->orderByTileID()->toArray(Utils::getToStringCallback()),
            'target' => $hand->getTarget()->exist()
                ? $hand->getTarget()->getTile()->toFormatString(true) : null,
            'melded' => $hand->getMelded()->toTileStringArrayArray(),
            'commands' => $commandProvider->getExecutableList($area->getSeatWind())
                ->toArray(Utils::getToStringCallback())
        ];
        return $a;
    }

    function toResultJson() {
        $round = $this->getRound();

        $a = [
            'isGameOver' => $round->isGameOver(),
            'isRoundOver' => $round->getPhase()->isOver(),
            'result' => null,
            'winReports' => [],
        ];

        if ($a['isRoundOver']) {
            $overPhaseResult = $round->getPhaseState()->getResult();

            $a['result'] = $overPhaseResult->__toString();

            if ($overPhaseResult->getResultType()->isWin()) {
                $a['winReports'] = $overPhaseResult->getWinReportList()
                    ->toArray([$this, 'toWinReportJson']);
            }
        }

        return $a;
    }

    function toWinReportJson(WinReport $winReport) {
        $a = [
            'actor' => $winReport->getActor()->__toString(),
            'fan' => $winReport->getFan(),
            'fu' => $winReport->getFu(),
            'yakuItems' => $winReport->getYakuItemList()->toArray(Utils::getToStringCallback()),
        ];
        return $a;
    }
}