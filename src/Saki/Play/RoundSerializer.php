<?php
namespace Saki\Play;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\Tile\Tile;
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
            'relations' => $this->toRelationsJson(),
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
     * @return array [$areaE, $areaS, $areaW, $areaN]
     */
    function toAreasJson() {
        $areaList = $this->getRound()->getAreaList();
        $toSeatWind = function (Area $area) {
            return $area->getSeatWind()->__toString();
        };
        return $areaList->toMap($toSeatWind, [$this, 'toAreaJson']);
    }

    /**
     * @return array e.x. ['prev' => 'E', 'self' => 'S', 'next' => 'W', 'towards' => 'N']
     */
    function toRelationsJson() {
        $areaList = $this->getRound()->getAreaList();
        $toRelation = function (Area $area) {
            return $this->getRole()->getRelation($area->getSeatWind());
        };
        $toSeatWind = function (Area $area) {
            return $area->getSeatWind()->__toString();
        };
        return $areaList->toMap($toRelation, $toSeatWind);
    }

    /**
     * @param Area $area
     * @return array
     */
    function toAreaJson(Area $area) {
        $role = $this->getRole();
        $actor = $area->getSeatWind();
        $hand = $area->getHand();
        $commandProvider = $this->getRound()->getProcessor()->getProvider();
        $provided = $commandProvider->provideAll()
            ->getActorProvided($area->getSeatWind())
            ->select(Utils::getToStringCallback());

        if ($role->mayExecute($actor)) {
            $commands = $provided->toArray();
        } else {
            $commands = [];
        }

        $toTileData = function (Tile $tile) use ($provided, $actor) {
            $discardCommand = "discard $actor $tile";
            $command = $provided->valueExist($discardCommand)
                ? $discardCommand
                : null;
            return [
                'tile' => $tile->__toString(),
                'command' => $command,
            ];
        };
        if ($role->mayViewHand($actor) || $this->getRound()->getPhase()->isOver()) {
            $public = $hand->getPublic()
                ->orderByTileID()
                ->toArray($toTileData);
            $target = $hand->getTarget()->existAndIsCreator($actor)
                ? $toTileData($hand->getTarget()->getTile())
                : ['tile' => 'X', 'command' => null];
        } else {
            $public = array_fill(0, $hand->getPublic()->count(), ['tile' => 'O', 'command' => null]);
            $target = $hand->getTarget()->existAndIsCreator($actor)
                ? ['tile' => 'O', 'command' => null]
                : ['tile' => 'X', 'command' => null];
        }

        $a = [
            'relation' => $role->getRelation($actor),
            'actor' => $actor->__toString(),
            'point' => $area->getPoint(),
            'isReach' => $area->getRiichiStatus()->isRiichi(),
            'discard' => $area->getDiscard()->toArray(Utils::getToStringCallback()),
            'wall' => $area->getActorWall()->toJson(),
            'commands' => $commands,
            'public' => $public,
            'target' => $target,
            'melded' => $hand->getMelded()->toTileStringArrayArray(),
        ];

        return $a;
    }

    /**
     * @return array
     */
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

    /**
     * @param WinReport $winReport
     * @return array
     */
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