var Saki = {
    VERSION: '0.0.1',
    debug: function (message) {
        console.log(message + ' ' + $.type(message) + ' ' + new Date().getMilliseconds());
    },
};

Saki.Game = function () {
    var isLocal = (window.location.href.search("localhost") != -1);
    this.url = isLocal ? 'ws://localhost:8080/' : 'ws://saki.ninja:8080/';
    this.conn = null;
    this.oninit = this.onupdate = this.onerror = function () {
    };
};
Saki.Game.prototype = {
    open: function () {
        if (this.conn === null) {
            this.conn = new WebSocket(this.url);
            this.conn.onopen = $.proxy(this.onopen, this);
            this.conn.onmessage = $.proxy(this.onmessage, this);
            this.conn.onerror; // todo
            this.conn.onclose; // todo
        } else {
            // do nothing
        }
    },
    send: function (command) {
        if (this.conn !== null) {
            this.conn.send(command);

            // todo move to view
            var log = $('.log');
            var msg = '---send---\n' + command + "\n";
            log.val(log.val() + msg);
        } else {
            throw new Error('Connection not ready.')
        }
    },
    close: function () {
        if (this.conn !== null) {
            this.conn.close();
            this.conn = null;
        } else {
            // do nothing
        }
    },
    onopen: function () {
        this.oninit();
    },
    onmessage: function (message) {
        var jsonString = message.data;
        var jsonData = JSON.parse(jsonString);

        // todo move to view
        var log = $('.log');
        var msg = '---receive---\n' + jsonString + "\n";
        log.val(log.val() + msg);

        if (jsonData.response !== 'ok') {
            this.onerror(jsonData);
            return;
        }

        this.onupdate(jsonData);
    }
};
Saki.Game.prototype.constructor = Saki.Game;

Saki.DemoView = function (game) {
    game.oninit = $.proxy(this.init, this);
    game.onupdate = $.proxy(this.render, this);
    game.onerror = $.proxy(this.error, this);
    this.game = game;
};
Saki.DemoView.prototype = {
    init: function () {
        Saki.debug('view.init()');
    },
    render: function (jsonData) {
        Saki.debug('view.render()');

        // todo clean code
        var roundData = jsonData.round;
        $('.prevailingWindContainer').html(this.actor(roundData.prevailingWind));
        $('.prevailingWindTurnContainer').html(roundData.prevailingWindTurn);
        $('.seatWindTurnContainer').html(roundData.seatWindTurn);
        $('.pointSticksContainer').html(roundData.pointSticks);

        var wall = jsonData.round.wall;
        $('.remainTileCountContainer').html(wall.remainTileCount);

        var result = jsonData.result;
        if (result.isRoundOver) {
            if (result.winReports) {
                var report = result.winReports[0];
                var resultText = result.result
                    + '\n' + [report.actor, report.fan + ' fan', report.fu + ' fu'].join(',')
                    + '\n' + report.yakuItems.join('\n');
            } else {
                var resultText = result.result;
            }

            var lastChangeDetailText = '';
            $.each(result.lastChangeDetail, function (actor, a) {
                lastChangeDetailText = lastChangeDetailText
                    + actor + ':' + [a.pre, a.change, a.now].join(',') + '\n';
            });

            if (result.isGameOver) {
                var finalScoreText = '';
                $.each(result.finalScore, function (notUsed, a) {
                    finalScoreText = finalScoreText
                        + [a.rank, a.seatWind, a.point, a.score].join(',') + '\n';
                });
            }

            $('.result').show();
            $('.indicatorWallContainer').html(this.indicatorWall(result.indicatorWall));
            $('.resultContainer').text(resultText);
            $('.lastChangeDetailContainer').text(lastChangeDetailText);
            $('.finalScoreContainer').text(finalScoreText);
        } else {
            $('.result').hide();
        }

        var that = this;
        var keys = [
            'actor', 'point',
            'discard', 'profile',
            'wall', 'commands',
            'public', 'target', 'melded'
        ];
        var areasData = jsonData['areas'];
        $.each(jsonData['areas'], function (i, areaData) {
            var area = $('.area-' + areaData.relation);
            $.each(keys, function (noUse, key) {
                var selector = '.' + key + 'Container';
                var html = that[key](areaData[key]);
                area.find(selector).html(html);
            });
        });
    },
    error: function (jsonData) {
    },
    /*-- round --*/
    remainTileCount: function (remainTileCountData) {
        return remainTileCountData;
    },
    /*-- actor --*/
    actor: function (tileData) {
        return this.tile(tileData)
            .addClass('tile-indicator');
    },
    point: function (pointData) {
        return pointData;
    },
    /*-- discard --*/
    discard: function (tilesData) {
        return $('<div class="discard"></div>')
            .append(tilesData.map($.proxy(this.tile, this)));
    },
    /*-- profile --*/
    profile: function (profileData) {
        return profileData.join('<br/>'); // todo remove temp
    },
    /*-- commands --*/
    commands: function (commandsData) {
        return $('<div class="commands"></div>')
            .append(commandsData.map($.proxy(this.command, this)));
    },
    command: function (commandData) {
        if (commandData.search('discard') != -1) {
            return '';
        }
        var send = $.proxy(this.game.send, this.game);
        return $('<input/>')
            .attr({
                class: 'command',
                type: 'button',
                value: commandData,
            })
            .click(function () {
                return send(this.value);
            });
    },
    /*-- wall --*/
    indicatorWall: function (indicatorWallData) {
        return $('<div class="indicatorWall"></div>')
            .append(this.discard(indicatorWallData.indicatorList))
            .append(this.discard(indicatorWallData.uraIndicatorList));
    },
    wall: function (stacksData) {
        return $('<div class="wall"></div>')
            .append(stacksData.map($.proxy(this.stack, this)));
    },
    stack: function (stackData) {
        return $('<span class="stack"></span>')
            .append(this.tileWall(stackData[0]))
            .append(this.tileWall(stackData[1]));
    },
    tileWall: function (tileData) {
        return this.tile(tileData)
            .addClass('tile-wall');
    },
    /*-- hand --*/
    public: function (tilesData) {
        return $('<span class="public"></span>')
            .append(tilesData.map($.proxy(this.tileDiscard, this)));
    },
    target: function (tileData) {
        return this.tileDiscard(tileData);
    },
    melded: function (meldedData) {
        return $('<span class="melded"></span>')
            .append(meldedData.reverse().map($.proxy(this.meld, this)));
    },
    meld: function (meldData) {
        var meld = $('<span class="meld"></span>');
        var isExtendKong = false;
        for (var i = 0; i < meldData.length; ++i) {
            var tileData = meldData[i];
            var tile = this.tile(tileData);
            if (isExtendKong && tileData[0] == '-') {
                tile.addClass('tile-extendKong-' + i);
            }
            meld.append(tile);

            if (tileData[0] == '-') {
                isExtendKong = true;
            }
        }
        return meld;
    },
    /*-- tile --*/
    tileDiscard: function (tileData) {
        var send = $.proxy(this.game.send, this.game);
        return $('<span></span>')
            .attr('class', 'tile tile-' + tileData.tile)
            .html(tileData)
            .attr('command', tileData.command)
            .click(function () {
                var command = $(this).attr('command');
                return command ? send(command) : false;
            });
    },
    tile: function (tileData) {
        var cls = 'tile tile-' + tileData;
        if (tileData[0] == '-') cls = cls + ' tile-';
        return $('<span></span>')
            .attr('class', cls)
            .html(tileData);
    },
};
Saki.DemoView.prototype.constructor = Saki.DemoView;

// execute
(function () {
    $(document).ready(function () {
        Saki.debug('$(document).ready');

        var game = new Saki.Game();
        var view = new Saki.DemoView(game);
        game.open();

        $('.command').click(function () {
            game.send(this.value);
        });

        $('#debugButton').click(function () {
            game.send($('#debugText').val());
        });
    });

    Saki.debug('imported js executed.');
})();