document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('message-form');
    const messageText = document.getElementById('message-text');
    const chatMessages = document.getElementById('chat-messages');
    
    // Função para adicionar mensagem ao chat
    function addMessageToChat(message, isAdmin = false) {
      const messageElement = document.createElement('div');
      messageElement.className = `message ${isAdmin ? 'admin' : 'user'}`;
      
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      
      messageElement.innerHTML = `
        <div class="message-info">
          <span class="message-sender">${isAdmin ? 'Suporte' : 'Você'}</span>
          <span class="message-time">${hours}:${minutes}</span>
        </div>
        <div class="message-content">
          <p>${message}</p>
        </div>
      `;
      
      chatMessages.appendChild(messageElement);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Enviar mensagem
    messageForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const message = messageText.value.trim();
      if (message === '') return;
      
      // Adicionar mensagem do usuário ao chat
      addMessageToChat(message, false);
      
      // Limpar campo de entrada
      messageText.value = '';
      
      // Enviar mensagem para o servidor
      sendMessageToServer(message);
    });
    
    // Função para enviar mensagem para o servidor
    function sendMessageToServer(message) {
      fetch('send_message.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(message)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          console.log('Mensagem enviada com sucesso');
        } else {
          console.error('Erro ao enviar mensagem:', data.message);
        }
      })
      .catch(error => {
        console.error('Erro na requisição:', error);
      });
    }
    
    // Função para verificar novas mensagens
    function checkNewMessages() {
      fetch('check_messages.php')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success' && data.messages.length > 0) {
          data.messages.forEach(msg => {
            addMessageToChat(msg.mensagem, msg.is_admin === "1" || msg.is_admin === 1);
          });
        }
      })
      .catch(error => {
        console.error('Erro ao verificar mensagens:', error);
      });
    }
    
    // Verificar novas mensagens a cada 5 segundos
    setInterval(checkNewMessages, 5000);
    
    // Verificar novas mensagens imediatamente ao carregar a página
    checkNewMessages();
  });
  