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
        $a = [];

        $areas = [];
        /** @var Area $area */
        foreach ($r->getAreaList() as $area) {
            $hand = $area->getHand();
            $actor = $area->getSeatWind();
            
            $areas[$actor->__toString()] = [
                'public' => $hand->getPublic()->toArray(Utils::getToStringCallback()),
                'target' => $hand->getTarget()->exist() ? $hand->getTarget()->getTile()->toFormatString(true) : null,
                'melded' => $hand->getMelded()->toArray(Utils::getToStringCallback()),
                'commands' => $commandProvider->getExecutableList($actor)->toArray(Utils::getToStringCallback()),
            ];
        }
        $a['areas'] = $areas;

        return json_encode($a);
    }
}