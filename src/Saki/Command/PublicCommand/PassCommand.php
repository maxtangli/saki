<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand
 */
class PassCommand extends PublicCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        return static::createMany($round, $actor, new ArrayList([[]]), true);
    }
    //endregion

    //region PublicCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return true;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $round->toNextPhase();
    }
    //endregion
}