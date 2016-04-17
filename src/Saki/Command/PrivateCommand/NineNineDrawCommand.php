<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

class NineNineDrawCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    protected function matchOther(CommandContext $context) {
        return $context->getTurn()->isFirstCircle()
        && !$context->getAreas()->getDeclareHistory()->hasDeclare()
        && $context->getActorHand()->getPrivate()->isNineKindsOfTerminalOrHonor();
    }

    protected function executeImpl(CommandContext $context) {
        $result = new AbortiveDrawResult($context->getRound()->getPlayerList()->toArray(),
            ResultType::create(ResultType::NINE_NINE_DRAW));
        $context->getRound()->toNextPhase(new OverPhaseState($result));
    }
}