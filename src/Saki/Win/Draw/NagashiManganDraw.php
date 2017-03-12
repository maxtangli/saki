<?php
namespace Saki\Win\Draw;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Win\Result\ExhaustiveDrawResult;
use Saki\Win\Result\NagashiManganDrawResult;

class NagashiManganDraw extends Draw {
    //region impl
    protected function isDrawImpl(Round $round) {
        if (ExhaustiveDraw::create()->isDraw($round)) {
            $isNagashiManganArea = function (Area $area) use($round) {
                return $this->isNagashiMangan($area->getSeatWind(), $round);
            };
            return $round->getAreaList()->any($isNagashiManganArea);
        }
        return false;
    }

    protected function getResultImpl(Round $round) {
        /** @var ExhaustiveDrawResult $exhaustiveDrawResult */
        $exhaustiveDrawResult = ExhaustiveDraw::create()->getResult($round);

        $keySelector = function (Area $area) {
            return $area->getSeatWind()->__toString();
        };
        $isNagashiManganArea = function (Area $area) use($round) {
            return $this->isNagashiMangan($area->getSeatWind(), $round);
        };
        $isNagashiManganMap = $round->getAreaList()->toMap($keySelector, $isNagashiManganArea);

        return new NagashiManganDrawResult($exhaustiveDrawResult, $isNagashiManganMap);
    }
    //endregion

    /**
     * @param SeatWind $actor
     * @param Round $round
     * @return bool
     */
    private function isNagashiMangan(SeatWind $actor, Round $round) {
        return $round->getTurnHolder()->getOpenHistory()
            ->isNagashiManganDiscard($actor);
    }
}