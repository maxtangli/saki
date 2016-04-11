<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Phase\OverPhaseState;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;

class WinByOtherCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    function matchOther() {
        return true; // todo
    }

    function executeImpl() {
        $round = $this->getContext()->getRound();

        $result = WinRoundResult::createWinByOther(
            $round->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $round->getWinResult($this->getActPlayer()),
            $this->getCurrentPlayer(),
            $round->getAreas()->getReachPoints() / 1000,
//            $round->getPrevailingCurrent()->getStatus()->getSeatWindTurn());
            $round->getAreas()->getDealerArea()->getSeatWindTurn());
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
}