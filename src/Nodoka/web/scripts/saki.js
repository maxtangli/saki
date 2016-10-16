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

        var that = this;
        var keys = [
            'actor', 'point',
            'discard',
            'public', 'target', 'melded',
            'commands'
        ];
        var areasData = jsonData['areas'];
        $.each(jsonData['areas'], function (i, areaData) {
            var area = $('#area' + ((i + 1)));
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
            .append(tilesData.map(this.tile));
    },
    /*-- commands --*/
    commands: function (commandsData) {
        return $('<div class="commands"></div>')
            .append(commandsData.map($.proxy(this.command, this)));
    },
    command: function (commandData) {
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
    /*-- wall --*/
    stack: function (stackData) {
        return $('<span class="stack"></span>')
            .append(this.tile(stackData[0]))
            .append(this.tile(stackData[1]));
    },
    wall: function (stacksData) {
        return $('<div class="wall"></div>')
            .append(stacksData.map($.proxy(this.stack, this)));
    },
    deadWall: function (deadWallData) {
        return this.wall(deadWallData);
    },
    /*-- hand --*/
    public: function (tilesData) {
        return $('<span class="public"></span>')
            .append(tilesData.map(this.tile));
    },
    target: function (tileData) {
        return tileData ? this.tile(tileData) : '';
    },
    melded: function (meldedData) {
        return $('<span class="melded"></span>')
            .append(meldedData.map($.proxy(this.meld, this)));
    },
    meld: function (meldData) {
        return $('<span class="meld"></span>')
            .append(meldData.map(this.tile));
    },
    /*-- tile --*/
    tile: function (tileData) {
        return $('<span></span>')
            .attr('class', 'tile tile-' + tileData)
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
    });

    Saki.debug('imported js executed.');
})();