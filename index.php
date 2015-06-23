<?php
require_once "bootstrap.php";
$server = new \Saki\App\Server();
$server->process();
$data = $server->getData();
$round = $data->getCurrentRound();
?>

<html>
<head></head>
<body>
<style type="text/css">
    .tg {
        border-collapse: collapse;
        border-spacing: 0;
        border-color: #aabcfe;
    }

    .tg td {
        font-family: Arial, sans-serif;
        font-size: 14px;
        padding: 10px 5px;
        border-style: solid;
        border-width: 1px;
        overflow: hidden;
        word-break: normal;
        border-color: #aabcfe;
        color: #669;
        background-color: #e8edff;
    }

    .tg th {
        font-family: Arial, sans-serif;
        font-size: 14px;
        font-weight: normal;
        padding: 10px 5px;
        border-style: solid;
        border-width: 1px;
        overflow: hidden;
        word-break: normal;
        border-color: #aabcfe;
        color: #039;
        background-color: #b9c9fe;
    }
</style>
<a href="index.php">refresh</a><br/>
<a href="index.php?reset=true">reset</a><br/>
<table class="tg">
    <tr>
        <th class="tg-031e">target</th>
        <th class="tg-031e">status</th>
        <th class="tg-031e">candidate</th>
    </tr>
    <tr>
        <td class="tg-031e">round</td>
        <td class="tg-031e" colspan="2">turn&nbsp;<?= todo ?></td>
    </tr>
    <tr>
        <td class="tg-031e">wall</td>
        <td class="tg-031e" colspan="2">remain&nbsp;<?= count($round->getWall()) ?></td>
    </tr>
    <?php foreach ($round->getPlayerList() as $player) {
        $playerArea = $round->getPlayerArea($player) ?>
        <tr>
            <td class="tg-031e" rowspan="3">player&nbsp;<?= $player ?></td>
            <td class="tg-031e">discarded(<?= count($playerArea->getDiscardedTileList()) ?>)&nbsp;<?= $playerArea->getDiscardedTileList() ?></td>
            <td class="tg-031e">candidate&nbsp;<?= $playerArea->hasCandidateTile() ? $playerArea->getCandidateTile() : '' ?></td>
        </tr>
        <tr>
            <td class="tg-031e">onHand(<?= count($playerArea->getOnHandTileSortedList()) ?>)&nbsp;<?= $playerArea->getOnHandTileSortedList() ?></td>
            <td class="tg-031e" rowspan="2">
                commands
                <form action="index.php">
                    <?php foreach($round->getCandidateCommand($player) as $command) {?>
                        <input type="submit" name="command" value="<?= $command ?>"/>
                    <?php } ?>
                </form>
            </td>
        </tr>
        <tr>
            <td class="tg-031e">exposed(<?= count($playerArea->getExposedMeldList()) ?>)&nbsp;<?= $playerArea->getExposedMeldList() ?></td>
        </tr>
    <?php } ?>
</table>
</body>
</html>

