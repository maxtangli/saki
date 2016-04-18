<?php

namespace Saki\Command;

use Saki\Command\Debug\MockDeadWallCommand;
use Saki\Command\Debug\MockHandCommand;
use Saki\Command\Debug\MockNextDrawCommand;
use Saki\Command\Debug\MockNextReplaceCommand;
use Saki\Command\Debug\PassAllCommand;
use Saki\Command\Debug\SkipCommand;
use Saki\Command\PrivateCommand\ConcealedKongCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\NineNineDrawCommand;
use Saki\Command\PrivateCommand\PlusKongCommand;
use Saki\Command\PrivateCommand\ReachCommand;
use Saki\Command\PrivateCommand\TsumoCommand;
use Saki\Command\PublicCommand\BigKongCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\PongCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Command\PublicCommand\SmallKongCommand;
use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;

class CommandSet extends ArrayList {
    use ReadonlyArrayList;
    private static $standardInstance;

    static function createStandard() {
        self::$standardInstance = self::$standardInstance ?? new self(
                [
                    // private
                    DiscardCommand::class,
                    ConcealedKongCommand::class,
                    PlusKongCommand::class,
                    ReachCommand::class,
                    TsumoCommand::class,
                    NineNineDrawCommand::class,
                    // public
                    ChowCommand::class,
                    PongCommand::class,
                    BigKongCommand::class,
                    RonCommand::class,
                    // debug
                    MockNextReplaceCommand::class,
                    MockDeadWallCommand::class,
                    MockHandCommand::class,
                    MockNextDrawCommand::class,
                    PassAllCommand::class,
                    SkipCommand::class,
                ]
            );
        return self::$standardInstance;
    }
}