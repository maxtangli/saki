<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand\PublicCommand
 */
class PassCommand extends PublicCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $otherParamsList = new ArrayList([[]]);
        return $otherParamsList;
    }
    //endregion

    //region PublicCommand impl
    protected function executablePlayerImpl(Round $round, Area $actorArea) {
        return true;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $round->toNextPhase();
    }
    //endregion
}