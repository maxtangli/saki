<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\Tile\Tile;

class BigKongCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind) {
        parent::__construct($context, [$playerSelfWind]);
    }

    function matchOtherConditions() {
        return true; // todo
    }

    function executeImpl() {
        $r = $this->getContext()->getRound();

        // avoid FourKongDraw by postLeave
        $postLeave = function () use ($r) {
            $r->getTileAreas()->bigKong(
                $this->getActPlayer(), $this->getCurrentPlayer()
            );
        };
        $r->getPhaseState()->setPostLeave($postLeave);

        $actPlayerPrivateState = new PrivatePhaseState($this->getActPlayer(), false);
        $r->toNextPhase($actPlayerPrivateState);
    }
}