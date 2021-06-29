console.log('-------------0');

var app     = require('express')(),
    http    = require('http').Server(app),
    io      = require('socket.io')(http);

var port = 3002,
    users = nicknames = {};

http.listen(port, function() {
    console.log('Listening on *:' + port);
});

console.log('-------------1');

io.on('connection', function (socket) {

    console.log('-------------2');

    socket.on('join', function (user) {

        console.info('New client connected (id=' + user.id + ' (' + user.name + ') => socket=' + socket.id + ').');

        // save socket to emit later on a specific one
        socket.room     = user.room;
        socket.userId   = user.id;
        socket.nickname = user.name;

        users[user.id] = socket;

        // roomに接続
        socket.join(socket.room);

        // store connected nicknames
        nicknames[user.id] = {
            'nickname': user.name,
            'socketId': socket.id,
        };

        function updateNicknames() {
            // send connected users to all sockets to display in nickname list
            io.sockets.to(socket.room).emit('chat.users', nicknames);
        }

        updateNicknames();

        // get user sent message and broadcast to all connected users
        socket.on('chat.send.message', function (message) {
            console.log('Receive message ' + message.msg + ' from user in channel chat.message');

            io.sockets.to(socket.room).emit('chat.message', JSON.stringify(message));
        });

        socket.on('chat.send.file', function (message) {
            console.log('Receive file ' + message.file + ' from user in channel chat.message');

            io.sockets.to(socket.room).emit('chat.file', JSON.stringify(message));
        });

        socket.on('chat.send.message.transfer', function (message) {
            console.log('Receive transfer message ' + message.msg + ' from user in channel chat.message');

            io.sockets.to('group_' + message.to_group_id).emit('chat.message', JSON.stringify(message));

            socket.leave('group_' + message.to_group_id);
        });

        socket.on('chat.delete.message', function (message) {
            console.log('Receive message_id ' + message.mid + ' from user in channel chat.delete');

            io.sockets.to(socket.room).emit('chat.delete', JSON.stringify(message));
        });

        socket.on('chat.delete.user', function (message) {
            console.log('Receive user_id ' + message.id + ' from user in channel chat.deleteUser');

            io.sockets.to(socket.room).emit('chat.deleteUser', JSON.stringify(message));
        });

        socket.on('disconnect', function() {
            if( ! socket.nickname) return;

            delete users[user.id];
            delete nicknames[user.id];

            updateNicknames();

            console.info('Client gone (id=' + user.id+ ' => socket=' + socket.id + ').');
        });

        socket.on('leave', function (room_name) {
            console.log('Leave room ' + room_name);

            socket.leave(room_name);
        });

        socket.on('chat.send.touch.xy', function (message) {
            console.log('Receive x = ' + message.x + ' y = ' + message.y);

            io.sockets.to(socket.room).emit('chat.touch.xy', JSON.stringify(message));
        });

    });
});