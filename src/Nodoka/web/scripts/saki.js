var Saki = {
    VERSION: '0.0.1',
    debug: function (message) {
        console.log(message + ' ' + $.type(message) + ' ' + new Date().getMilliseconds());
    },
    css: function (on) {
        if (on) {
            $('link[rel="stylesheet"]').removeAttr('disabled');
        } else {
            $('link[rel="stylesheet"]').attr('disabled', 'disabled');
        }
    }
};

Saki.Game = function () {
    // this.url = 'ws://ec2-52-198-24-187.ap-northeast-1.compute.amazonaws.com:8080/';
    this.url = 'ws://localhost:8080/';
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

        if (jsonData.result !== 'ok') {
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

        var wall = jsonData.round.wall;
        $('.remainTileCountContainer').html(wall.remainTileCount);
        $('.deadWallContainer').html(this.deadWall(wall.stacks));

        var areaKeys = [
            'actor', 'point', 'isReach',
            'discard',
            'public', 'target', 'melded',
            'commands'
        ];
        var areasData = jsonData['areas'];
        for (var i = 0; i < areasData.length; ++i) {
            var areaData = areasData[i];
            var area = $('#area' + ((i + 1)));
            for (var j = 0; j < areaKeys.length; ++j) {
                var key = areaKeys[j];
                var selector = '.' + key + 'Container';
                var html = this[key](areaData[key]);
                area.find(selector).html(html);
            }
        }
    },
    error: function (jsonData) {
    },
    remainTileCount: function (remainTileCountData) {
        return remainTileCountData;
    },
    actor: function (tileData) {
        return tileData;
    },
    point: function (pointData) {
        return pointData;
    },
    isReach: function (isReach) {
        return isReach ? 'true' : 'false';
    },
    discard: function (tilesData) {
        return $('<ol class="discard"></ol>')
            .append(
                tilesData.map($.proxy(this.tileLi, this))
            );
    },
    public: function (tilesData) {
        return $('<ol class="public"></ol>')
            .append(
                tilesData.map($.proxy(this.tileLi, this))
            );
    },
    target: function (tileData) {
        return tileData ? this.tile(tileData) : '';
    },
    command: function (commandData) {
        // <input class="command" type="button" value="discard 7m"/>
        var send = $.proxy(this.game.send, this.game);
        return $('<input/>')
            .attr({
                class: 'command',
                type: 'button',
                value: commandData
            })
            .click(function () {
                return send(this.value);
            });
    },
    commandLi: function (commandData) {
        return $('<li></li>')
            .append(
                this.command(commandData)
            );
    },
    commands: function (commandsData) {
        return $('<ol class="commands"></ol>')
            .append(
                commandsData.map($.proxy(this.commandLi, this))
            );
    },
    tile: function (tileData) {
        // <span class="tile tile-7m">7m</span>
        return $('<span></span>')
            .attr('class', 'tile tile-' + tileData)
            .html(tileData);
    },
    tileLi: function (tileData) {
        return $('<li></li>')
            .append(this.tile(tileData));
    },
    meld: function (meldData) {
        return $('<ol class="meld"></ol>')
            .append(
                meldData.map($.proxy(this.tileLi, this))
            );
    },
    meldLi: function (meldData) {
        return $('<li></li>')
            .append(this.meld(meldData));
    },
    melded: function (meldedData) {
        return $('<ol class="melded"></ol>')
            .append(
                meldedData.map($.proxy(this.meldLi, this))
            );
    },
    stack: function (stackData) {
        var stackTop = $('<li class="stack-top"></li>')
            .append(this.tile(stackData[0]));
        var stackBottom = $('<li class="stack-bottom"></li>')
            .append(this.tile(stackData[1]));
        return $('<ol class="stack"></ol>')
            .append(stackTop)
            .append(stackBottom);
    },
    stackLi: function (stackData) {
        return $('<li></li>')
            .append(this.stack(stackData));
    },
    wall: function (stacksData) {
        return $('<ol class="wall"></ol>')
            .append(
                stacksData.map($.proxy(this.stackLi, this))
            );
    },
    deadWall: function (deadWallData) {
        return this.wall(deadWallData);
    },
};
Saki.DemoView.prototype.constructor = Saki.DemoView;

// execute
(function () {
    $(document).ready(function () {
        var cssSwitch = (function () {
            var on = true;
            return function () {
                on = !on;
                Saki.css(on);
                $(this).val(on ? 'css off' : 'css on');
            };
        })();
        $('#debug_cssSwitch').click(cssSwitch);

        Saki.debug('$(document).ready');
        var game = new Saki.Game();
        var view = new Saki.DemoView(game);
        game.open();

        $('.command').click(function () {
            game.send(this.value);
        });
    });

    Saki.debug('imported js executed.');
})();