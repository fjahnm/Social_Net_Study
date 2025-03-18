const socket = io('http://localhost:3000'); // Substitua pelo endereço do seu servidor, se necessário

// Manipulador para carregar mensagens existentes ao conectar
socket.on('load_messages', (messages) => {
    const messagesList = document.getElementById('messages-list');

    // Adicionar mensagens antigas à lista
    messages.forEach((data) => {
        const newMessage = document.createElement('div');
        newMessage.classList.add('message', data.sender_id === 'self' ? 'sent' : 'received');
        newMessage.innerHTML = `
            <p>${data.message_content}</p>
            <span class="message-time">${new Date(data.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
        `;
        messagesList.appendChild(newMessage);
    });

    // Rolar para o final automaticamente
    messagesList.scrollTop = messagesList.scrollHeight;
});

// Enviar nova mensagem
document.getElementById('message-form').addEventListener('submit', (e) => {
    e.preventDefault();

    const messageInput = document.getElementById('message-content');
    const message = messageInput.value.trim();
    const receiver_id = document.querySelector('input[name="receiver_id"]').value;

    if (message) {
        // Enviar mensagem para o servidor
        socket.emit('new_message', {
            sender_id: 'self', // Identificador do remetente (substitua por algo dinâmico, por exemplo, o ID do usuário)
            receiver_id: receiver_id,
            message_content: message,
            created_at: new Date().toISOString(),
        });

        // Exibir imediatamente no frontend (opcional)
        const messagesList = document.getElementById('messages-list');
        const newMessage = document.createElement('div');
        newMessage.classList.add('message', 'sent');
        newMessage.innerHTML = `
            <p>${message}</p>
            <span class="message-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
        `;
        messagesList.appendChild(newMessage);

        // Limpar o campo de entrada
        messageInput.value = '';
        messagesList.scrollTop = messagesList.scrollHeight;
    }
});

// Receber mensagem nova de outros usuários
socket.on('receive_message', (data) => {
    const messagesList = document.getElementById('messages-list');
    const newMessage = document.createElement('div');
    newMessage.classList.add('message', 'received');
    newMessage.innerHTML = `
        <p>${data.message_content}</p>
        <span class="message-time">${new Date(data.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
    `;
    messagesList.appendChild(newMessage);
    messagesList.scrollTop = messagesList.scrollHeight; // Rola para o final automaticamente
});