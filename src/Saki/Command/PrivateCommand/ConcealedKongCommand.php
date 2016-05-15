<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Claim;
use Saki\Game\SeatWind;
use Saki\Meld\QuadMeldType;
use Saki\Tile\Tile;

class ConcealedKongCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind, Tile $tile) {
        parent::__construct($context, [$playerSeatWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    protected function matchOther(CommandContext $context) {
        return true; // todo
    }

    protected function executeImpl(CommandContext $context) {
        $area = $context->getActorArea();
        $actor = $this->getActor();
        $turn = $context->getTurn();

        $tile = $this->getTile();
        $tiles = [$tile, $tile, $tile, $tile];
        $claim = Claim::create($actor, $turn,
            $tiles, QuadMeldType::create(), true
        );
        $claim->apply($area);

//        $context->getActorArea()->concealedKong($this->getTile());
        // stay in private phase
    }
}