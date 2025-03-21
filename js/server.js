const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql2');

const app = express();
const server = http.createServer(app);
const io = socketIo(server);

const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'social_network'
});

db.connect((err) => {
    if (err) {
        console.error('Erro de conexão com o banco de dados:', err);
    } else {
        console.log('Conectado ao banco de dados!');
    }
});

io.on('connection', (socket) => {
    console.log('Novo usuário conectado');

    db.query('SELECT * FROM messages ORDER BY created_at ASC', (err, results) => {
        if (err) {
            console.error('Erro ao recuperar mensagens:', err);
        } else {
            socket.emit('load_messages', results);
        }
    });

    socket.on('new_message', (data) => {
        const { sender_id, receiver_id, message_content } = data;
        const created_at = new Date().toISOString();

        db.query(
            'INSERT INTO messages (sender_id, receiver_id, message_content, created_at, read_status) VALUES (?, ?, ?, ?, ?)',
            [sender_id, receiver_id, message_content, created_at, 'unread'],
            (err, result) => {
                if (err) {
                    console.error('Erro ao inserir mensagem:', err);
                } else {
                    console.log('Mensagem inserida com sucesso:', result);

                    io.emit('receive_message', {
                        sender_id,
                        receiver_id,
                        message_content,
                        created_at
                    });
                }
            }
        );
    });

    socket.on('disconnect', () => {
        console.log('Usuário desconectado');
    });
});

server.listen(3000, () => {
    console.log('Servidor WebSocket rodando na porta 3000');
});
