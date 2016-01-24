<?php
require_once __DIR__.'/../bootstrap.php';
use Saki\Command\CommandContext;
use Saki\Command\DiscardCommand;
use Saki\Game\Round;
use Saki\Tile\Tile;
use Saki\Tile\TileSet;
use Saki\Util\MsTimer;

MsTimer::getInstance()->restart();
$context = new CommandContext(new Round());
$line = 'discard E 1p';
$obj = DiscardCommand::fromString($context, $line);
MsTimer::getInstance()->restartWithDump();
echo "finished.";