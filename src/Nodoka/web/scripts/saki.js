var Saki = {
    VERSION: '0.0.1',
    bind: function (object, method) {
        return function () {
            method.apply(object, arguments);
        };
    },
    debug: function (message) {
        console.log(message + ' ' + jQuery.type(message) + ' ' + new Date().getMilliseconds());
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
    this.view = view; // Requires init(), render()
};
Saki.Game.prototype = {
    open: function () {
        if (this.conn === null) {
            this.conn = new WebSocket(this.url);
            this.conn.onopen = Saki.bind(this, this.onopen);
            this.conn.onmessage = Saki.bind(this, this.onmessage);
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

        var publicData = jsonData.areas[0].public;
        var hand = $('#area1 .hand');
        // hand.html(publicData.toString());
        // todo
    },
    error: function (jsonData) {
    }
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