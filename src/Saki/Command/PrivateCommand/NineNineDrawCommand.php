<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

/**
 * @package Saki\Command\PrivateCommand
 */
class NineNineDrawCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }
    //endregion

    /**
     * @param CommandContext $context
     * @param SeatWind $actor
     */
    function __construct(CommandContext $context, SeatWind $actor) {
        parent::__construct($context, [$actor]);
    }

    //region PrivateCommand impl
    protected function matchOther(CommandContext $context, Area $actorArea) {
        return $context->getAreas()->getTurn()->isFirstCircle()
        && !$context->getAreas()->getClaimHistory()->hasClaim()
        && $actorArea->getHand()->getPrivate()->isNineKindsOfTermOrHonour();
    }

    protected function executePlayerImpl(CommandContext $context, Area $actorArea) {
        $result = new AbortiveDrawResult(
            $context->getAreas()->getGameData()->getPlayerType(),
            ResultType::create(ResultType::NINE_NINE_DRAW)
        );
        $context->getRound()->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}