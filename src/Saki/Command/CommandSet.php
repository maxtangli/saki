<?php

namespace Saki\Command;

use Saki\Command\DebugCommand\DoubleRonCommand;
use Saki\Command\DebugCommand\InitCommand;
use Saki\Command\DebugCommand\MockIndicatorWallCommand;
use Saki\Command\DebugCommand\MockHandCommand;
use Saki\Command\DebugCommand\MockNextDrawCommand;
use Saki\Command\DebugCommand\MockNextReplaceCommand;
use Saki\Command\DebugCommand\MockWallRemainCommand;
use Saki\Command\DebugCommand\PassAllCommand;
use Saki\Command\DebugCommand\SkipCommand;
use Saki\Command\DebugCommand\SkipToLastCommand;
use Saki\Command\DebugCommand\SkipToCommand;
use Saki\Command\DebugCommand\ToGameOverCommand;
use Saki\Command\DebugCommand\ToNextRoundCommand;
use Saki\Command\DebugCommand\TripleRonCommand;
use Saki\Command\PrivateCommand\ConcealedKongCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\ExtendKongCommand;
use Saki\Command\PrivateCommand\NineNineDrawCommand;
use Saki\Command\PrivateCommand\RiichiCommand;
use Saki\Command\PrivateCommand\TsumoCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\KongCommand;
use Saki\Command\PublicCommand\PassCommand;
use Saki\Command\PublicCommand\PungCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;

/**
 * @package Saki\Command
 */
class CommandSet extends ArrayList {
    use ReadonlyArrayList;
    private static $standardInstance;

    /**
     * @return CommandSet
     */
    static function createStandard() {
        self::$standardInstance = self::$standardInstance ?? new self([
                // debug
                InitCommand::class,
                DoubleRonCommand::class,
                MockIndicatorWallCommand::class,
                MockHandCommand::class,
                MockNextDrawCommand::class,
                MockNextReplaceCommand::class,
                MockWallRemainCommand::class,
                PassAllCommand::class,
                SkipCommand::class,
                SkipToLastCommand::class,
                SkipToCommand::class,
                ToGameOverCommand::class,
                ToNextRoundCommand::class,
                TripleRonCommand::class,
                // private
                DiscardCommand::class,
                ConcealedKongCommand::class,
                ExtendKongCommand::class,
                RiichiCommand::class,
                TsumoCommand::class,
                NineNineDrawCommand::class,
                // public
                ChowCommand::class,
                PungCommand::class,
                KongCommand::class,
                PassCommand::class,
                RonCommand::class,
            ]);
        return self::$standardInstance;
    }

    /**
     * @return CommandSet
     */
    function toPlayerCommandSet() {
        $isPlayerCommand = function (string $class) {
            return is_subclass_of($class, PlayerCommand::class);
        };
        return new self(
            $this->toArrayList()->where($isPlayerCommand)->toArray()
        );
    }
}