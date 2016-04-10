<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;

class BigKongCommand extends PublicCommand {
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
        $r = $this->getContext()->getRound();

        // avoid FourKongDraw by postLeave
        $postLeave = function () use ($r) {
            $r->getAreas()->bigKong(
                $this->getActPlayer(), $this->getCurrentPlayer()
            );
        };
        $r->getPhaseState()->setPostLeave($postLeave);

        $actPlayerPrivateState = new PrivatePhaseState($this->getActPlayer(), false);
        $r->toNextPhase($actPlayerPrivateState);
    }
}