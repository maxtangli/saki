<?php
namespace Saki\Game;
use Saki\Util\Utils;

/**
 * @package Saki\Game
 */
class RoundSerializer {
    private function __construct() {
    }

    /**
     * @param Round $r
     * @return string
     */
    static function toJson(Round $r) { // todo
        $commandProvider = $r->getProcessor()->getProvider();

        $prevailing = $r->getPrevailing();
        $phase = $r->getPhase();
        $round = [
            'prevailing' => $prevailing->__toString(),
            'prevailingWind' => $prevailing->getStatus()->getPrevailingWind()->__toString(),
            'prevailingWindTurn' => $prevailing->getStatus()->getPrevailingWindTurn(),
            'seatWindTurn' => $prevailing->getSeatWindTurn(),
            'phase' => $phase->__toString(),
            'result' => $phase->isOver() ? $r->getPhaseState()->getResult()->__toString() : null,
        ];

        $areas = [];
        /** @var Area $area */
        foreach ($r->getAreaList() as $area) {
            $hand = $area->getHand();
            $actor = $area->getSeatWind();

            $areas[] = [
                'actor' => $actor->__toString(),
                'discard' => $area->getDiscard()->toArray(Utils::getToStringCallback()),
                'public' => $hand->getPublic()->toArray(Utils::getToStringCallback()),
                'target' => $hand->getTarget()->exist() ? $hand->getTarget()->getTile()->toFormatString(true) : null,
                'melded' => $hand->getMelded()->toArray(Utils::getToStringCallback()),
                'isReach' => $area->getRiichiStatus()->isRiichi(),
                'point' => $area->getPoint(),
                'commands' => $commandProvider->getExecutableList($actor)->toArray(Utils::getToStringCallback()),
            ];
        }
        
        $a = [
            'result' => 'ok',
            'round' => $round,
            'areas' => $areas,
        ];
        
        return json_encode($a);
    }
}