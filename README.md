# saki

Goal: A japanese-mahjong game server + browser client.

Develop progress

function   | progress | remark
---------- | -------- | ------
rule logic | 95%      | php7, phpunit
network    | 1%,ing   | websocket, Racket(php library)
UI         | 0%       | html5 canvas, to support any devices with a browser
DB         | 0%       | nosql, since not so frequently db access
src reading| 0%       | read open source mahjong projects to improve
release    | 0%       | wish to achieve in 2016

## install

Requirement: php7, composer.

1. https://github.com/maxtangli/saki.git
2. cd saki
3. composer install
4. setup server: php bin/server.php
5. setup client: open src/Nodoka/web/index.html with any browser [supports websocket!](https://www.websocket.org/echo.html).

## usage

![demo UI by 2016-07-09](https://github.com/maxtangli/saki/blob/master/reference/Nodoka_dev.png)

In fieldset "gui(debug ver)"
- Round state and candidate commands is displayed.
- Click buttons to send commands. Currently any client can send any commands.

After a "ron" or "tsumo" command,
- Round results displayed, points changed.
- If not gameover, click "toNextRound" to enter next round.
- If gameover, click "init" to restart game.