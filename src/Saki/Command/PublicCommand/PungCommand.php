<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Claim;
use Saki\Game\SeatWind;
use Saki\Meld\TripleMeldType;
use Saki\Phase\PrivatePhaseState;

class PungCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    protected function matchOther(CommandContext $context) {
        return true; // todo
    }

    protected function executeImpl(CommandContext $context) {
        $area = $context->getActorArea();
        $actor = $this->getActor();
        $turn = $context->getTurn();

        $targetTile = $area->getHand()->getTarget()->getTile();
        $tiles = [$targetTile, $targetTile, $targetTile];
        $claim = Claim::create($actor, $turn,
            $tiles, TripleMeldType::create(), false
        );

        $context->getRound()->toNextPhase(
            new PrivatePhaseState($actor, false, false, $claim)
        );

//        $area = $context->getActorArea();
//        $postEnter = function () use($area) {
//            return $area->pung();
//        };
//        $context->getRound()->toNextPhase(
//            new PrivatePhaseState($this->getActor(), false, false, $postEnter)
//        );
    }
}