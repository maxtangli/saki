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

Saki.Game = function (view) {
    this.url = 'ws://ec2-52-198-24-187.ap-northeast-1.compute.amazonaws.com:8080/';
    this.conn = null;
    this.view = view; // View: init(), render(), error()
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
    onopen: function () {
        this.view.init();
    },
    send: function (command) {
        this.conn.send(command);
    },
    onmessage: function (message) {
        var jsonString = message.data;
        var jsonData = JSON.parse(jsonString);

        if (jsonData.result !== 'ok') {
            this.view.error(jsonData);
            return;
        }

        this.view.render(jsonData);
    },
    close: function () {
        if (this.conn !== null) {
            this.conn.close();
            this.conn = null;
        } else {
            // do nothing
        }
    }
};
Saki.Game.prototype.constructor = Saki.Game;

Saki.DemoView = function () {

};
Saki.DemoView.prototype = {
    init: function () {
        Saki.debug('view.init()');
    },
    render: function (jsonData) {
        Saki.debug('view.render()');

        var areaData = jsonData.areas[0];
        var area = $('#area1');

        area
            .find('.actorContainer')
            .empty().append(this.actor(areaData.actor)).end()
            .find('.pointContainer')
            .empty().append(this.point(areaData.point)).end()
            .find('.isReachContainer')
            .empty().append(this.isReach(areaData.isReach)).end()
            .find('.discardContainer')
            .empty().append(this.discard(areaData.discard)).end()
            .find('.publicContainer')
            .empty().append(this.public(areaData.public)).end()
            .find('.meldedContainer')
            .empty().append(this.melded(areaData.melded)).end()
            .find('.commandsContainer')
            .empty().append(this.commands(areaData.commands)).end()
    },
    error: function (jsonData) {
    },
    tile: function (tileData) {
        // <span class="tile tile-7m">7m</span>
        return $('<span></span>')
            .attr('class', 'tile tile-' + tileData)
            .html(tileData);
    },
    tileLi: function (tileData) {
        return this.tile(tileData)
            .wrap('<li></li>')
            .parent();
        // return $('<li></li>')
        //     .append(this.tile(tileData));
    },
    actor: function (tileData) {
        return this.tile(tileData);
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
    melded: function (meldsData) {
        return $('<ol class="melded"></ol>')
            .append(
                meldsData.map($.proxy(this.meldLi, this))
            );
    },
    command: function (commandData) {
        // <input class="command" type="button" value="discard 7m"/>
        return $('<input/>')
            .attr({
                class: 'command',
                type: 'button',
                value: commandData
            });
    },
    commandLi: function (commandData) {
        return $('<li></li>')
            .append(this.command(commandData));
    },
    commands: function (commandsData) {
        return $('<ol class="commands"></ol>')
            .append(
                commandsData.map($.proxy(this.commandLi, this))
            );
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
        $('#cssSwitcher').click(cssSwitch);

        Saki.debug('$(document).ready');
        var view = new Saki.DemoView();
        var game = new Saki.Game(view);
        game.open();
    });

    Saki.debug('js');
})();