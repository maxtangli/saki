<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Phase\OverPhaseState;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;

class WinBySelfCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    function matchOther() {
        // todo
        return true;
    }

    function executeImpl() {
        $round = $this->getContext()->getRound();

        $result = WinRoundResult::createWinBySelf(
            $round->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $round->getWinResult($this->getActPlayer()),
            $round->getAreas()->getReachPoints() / 1000,
//            $round->getPrevailingCurrent()->getStatus()->getSeatWindTurn()
            $round->getAreas()->getDealerArea()->getSeatWindTurn()
        );
        $round->toNextPhase(new OverPhaseState($result));
    }
}