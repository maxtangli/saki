<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinState;

/**
 * @package Saki\Command\PrivateCommand
 */
class TsumoCommand extends PrivateCommand {
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
        $winReport = $context->getAreas()->getWinReport($this->getActor());
        return $winReport->getWinState()->getValue() == WinState::WIN_BY_SELF;
    }

    protected function executePlayerImpl(CommandContext $context, Area $actorArea) {
        $round = $context->getRound();
        $actor = $this->getActor();
        $areas = $round->getAreas();

        $areas->getWall()->getDeadWall()->openUraDoraIndicator();

        $result = new WinResult(WinResultInput::createTsumo(
            [$actor, $round->getAreas()->getWinReport($actor)->getFanAndFu()],
            $areas->getOtherSeatWinds([$actor]),
            $areas->getRiichiHolder()->getRiichiPoints(),
            $context->getAreas()->getPrevailingCurrent()->getSeatWindTurn()
        ));
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}