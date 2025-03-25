document.addEventListener('DOMContentLoaded', function() {
  // Navegação da sidebar
  const navLinks = document.querySelectorAll('.sidebar-nav a');
  const sections = document.querySelectorAll('.dashboard-section');
  
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href').substring(1);
      
      // Remover classe active de todos os links e adicionar ao clicado
      navLinks.forEach(navLink => {
        navLink.parentElement.classList.remove('active');
      });
      this.parentElement.classList.add('active');
      
      // Esconder todas as seções e mostrar a selecionada
      sections.forEach(section => {
        section.classList.remove('active');
      });
      document.getElementById(targetId).classList.add('active');
    });
  });
  
  // Header scroll effect
  const header = document.querySelector('header');
  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
  
  // Menu móvel
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const mobileMenu = document.querySelector('.mobile-menu');

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', function() {
      menuToggle.classList.toggle('active');
      mobileMenu.classList.toggle('active');
      
      // Animação do ícone do menu
      const spans = menuToggle.querySelectorAll('span');
      if (menuToggle.classList.contains('active')) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
      } else {
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
      }
    });
  }
  
  // Fechar o menu ao clicar em um link
  const mobileMenuLinks = document.querySelectorAll('.mobile-menu a');
  mobileMenuLinks.forEach(link => {
    link.addEventListener('click', function() {
      menuToggle.classList.remove('active');
      mobileMenu.classList.remove('active');
      
      // Reset menu icon
      const spans = menuToggle.querySelectorAll('span');
      spans[0].style.transform = 'none';
      spans[1].style.opacity = '1';
      spans[2].style.transform = 'none';
    });
  });
  
  // Funções para modais
  window.showModal = function(type) {
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const modalFields = document.getElementById('modal-fields');
    const modalForm = document.getElementById('modal-form');

    // Limpar campos anteriores
    modalFields.innerHTML = '';

    // Configurar modal com base no tipo
    switch (type) {
      case 'password':
        modalTitle.textContent = 'Alterar Senha';
        modalFields.innerHTML = `
          <input type="password" name="current_password" placeholder="Senha atual" required>
          <input type="password" name="new_password" placeholder="Nova senha" required>
          <input type="password" name="confirm_password" placeholder="Confirmar nova senha" required>
          <input type="hidden" name="action" value="change_password">
        `;
        break;
      case 'email':
        modalTitle.textContent = 'Alterar Email';
        modalFields.innerHTML = `
          <input type="email" name="new_email" placeholder="Novo email" required>
          <input type="password" name="password" placeholder="Confirmar senha" required>
          <input type="hidden" name="action" value="change_email">
        `;
        break;
      case 'name':
        modalTitle.textContent = 'Alterar Nome';
        modalFields.innerHTML = `
          <input type="text" name="new_name" placeholder="Novo nome" required>
          <input type="hidden" name="action" value="change_name">
        `;
        break;
      case 'address':
        modalTitle.textContent = 'Adicionar Morada';
        modalFields.innerHTML = `
          <input type="text" name="rua" placeholder="Rua" required>
          <input type="text" name="numero" placeholder="Número" required>
          <input type="text" name="cidade" placeholder="Cidade" required>
          <input type="text" name="codigo_postal" placeholder="Código Postal" required>
          <input type="hidden" name="action" value="add_address">
        `;
        break;
      case 'phone':
        modalTitle.textContent = 'Adicionar Telefone';
        modalFields.innerHTML = `
          <input type="tel" name="phone" placeholder="Número de telefone" required>
          <input type="hidden" name="action" value="add_phone">
        `;
        break;
    }

    // Mostrar o modal com animação
    modal.style.display = 'block';
    setTimeout(() => {
      modal.classList.add('show');
    }, 10);

    // Configurar o envio do formulário
    modalForm.onsubmit = function(e) {
      e.preventDefault();
      const formData = new FormData(modalForm);
      
      // Mostrar indicador de carregamento
      const submitBtn = modalForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
      submitBtn.disabled = true;
      
      fetch(window.location.href, {
        method: 'POST',
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Erro na resposta do servidor');
        }
        return response.json();
      })
      .then(data => {
        if (data.status === 'success') {
          showNotification(data.message, 'success');
          closeModal();
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          throw new Error(data.message);
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro: ' + error.message, 'error');
        
        // Restaurar botão
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    };
  };

  window.closeModal = function() {
    const modal = document.getElementById('modal');
    modal.classList.remove('show');
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300);
  };

  // Função para mostrar os detalhes do pedido
  window.showOrderDetails = function(orderId) {
    const modal = document.getElementById('order-details-modal');
    const modalContent = document.getElementById('order-details-content');
    
    // Mostrar indicador de carregamento
    modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Carregando detalhes...</div>';
    
    // Mostrar o modal com animação
    modal.style.display = 'block';
    setTimeout(() => {
      modal.classList.add('show');
    }, 10);
    
    // Buscar detalhes do pedido
    fetch(`get_order_details.php?order_id=${orderId}`)
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          modalContent.innerHTML = `<div class="error-message">${data.error}</div>`;
          return;
        }

        // Construir o HTML para os itens do pedido
        let html = '<div class="order-details">';
        
        // Informações do pedido
        html += `
          <div class="order-info">
            <div class="order-header">
              <h3>Pedido #${data.order.id}</h3>
              <span class="status-badge status-${data.order.status}">
                ${data.order.status === 'pendente' ? 'Pendente' : 
                  data.order.status === 'enviado' ? 'Enviado' : 'Recebido'}
              </span>
            </div>
            <div class="order-meta">
              <p><i class="far fa-calendar-alt"></i> Data: ${new Date(data.order.data_compra).toLocaleDateString()}</p>
              <p><i class="fas fa-star"></i> Pontos ganhos: ${data.order.pontos_ganhos}</p>
            </div>
          </div>
        `;
        
        // Itens do pedido
        html += '<div class="order-items">';
        html += '<h4>Itens do Pedido</h4>';
        
        data.items.forEach(item => {
          html += `
            <div class="order-item">
              <div class="item-image">
                <img src="${item.imagem}" alt="${item.nome}">
              </div>
              <div class="item-details">
                <h5>${item.nome}</h5>
                <div class="item-meta">
                  <p><span>Quantidade:</span> ${item.quantidade}</p>
                  <p><span>Preço unitário:</span> €${parseFloat(item.preco_unitario).toFixed(2).replace('.', ',')}</p>
                  <p><span>Subtotal:</span> €${parseFloat(item.subtotal).toFixed(2).replace('.', ',')}</p>
                </div>
              </div>
            </div>
          `;
        });
        
        html += '</div>';
        
        // Resumo do pedido
        html += `
          <div class="order-summary">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span>€${parseFloat(data.order.valor_compra).toFixed(2).replace('.', ',')}</span>
            </div>
            <div class="summary-row">
              <span>Frete:</span>
              <span>€0,00</span>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <span>€${parseFloat(data.order.valor_compra).toFixed(2).replace('.', ',')}</span>
            </div>
          </div>
          
          <div class="order-actions">
            <button class="btn-primary" onclick="generateInvoice(${data.order.id})">
              <i class="fas fa-file-invoice"></i> Gerar Fatura
            </button>
          </div>
        `;
        
        html += '</div>';
        
        modalContent.innerHTML = html;
      })
      .catch(error => {
        console.error('Erro ao buscar detalhes do pedido:', error);
        modalContent.innerHTML = '<div class="error-message">Ocorreu um erro ao buscar os detalhes do pedido.</div>';
      });
  };

  window.closeOrderDetailsModal = function() {
    const modal = document.getElementById('order-details-modal');
    modal.classList.remove('show');
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300);
  };

  // Função para gerar fatura
  window.generateInvoice = function(orderId) {
    window.location.href = `generate_invoice.php?order_id=${orderId}`;
  };
  document.addEventListener('DOMContentLoaded', function() {
    window.cancelOrder = function(orderId) {
      if (confirm('Tem certeza que deseja cancelar este pedido?')) {
        // Mostrar indicador de carregamento
        showNotification('Processando cancelamento...', 'info');
        
        fetch('cancel_order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `order_id=${orderId}&action=cancel_order`
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            showNotification(data.message, 'success');
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showNotification('Erro: ' + data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          showNotification('Erro ao processar o cancelamento', 'error');
        });
      }
    };
  });
  

  // Notificações
  window.showNotification = function(message, type = 'success') {
    // Remover notificações existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
      notification.remove();
    });
    
    // Criar nova notificação
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
      <div class="notification-icon">
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
      </div>
      <div class="notification-message">${message}</div>
      <button class="notification-close"><i class="fas fa-times"></i></button>
    `;
    
    document.body.appendChild(notification);
    
    // Mostrar notificação com animação
    setTimeout(() => {
      notification.classList.add('show');
    }, 10);
    
    // Configurar botão de fechar
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
      notification.classList.remove('show');
      setTimeout(() => {
        notification.remove();
      }, 300);
    });
    
    // Auto-fechar após 5 segundos
    setTimeout(() => {
      if (document.body.contains(notification)) {
        notification.classList.remove('show');
        setTimeout(() => {
          if (document.body.contains(notification)) {
            notification.remove();
          }
        }, 300);
      }
    }, 5000);
  };
  
  // Filtro de compras
  const searchInput = document.getElementById('search-purchases');
  const filterStatus = document.getElementById('filter-status');
  
  if (searchInput && filterStatus) {
    const filterPurchases = () => {
      const searchTerm = searchInput.value.toLowerCase();
      const statusFilter = filterStatus.value;
      
      const purchaseRows = document.querySelectorAll('.purchases-table tbody tr');
      
      purchaseRows.forEach(row => {
        const id = row.querySelector('td:first-child').textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        const matchesSearch = id.includes(searchTerm);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    };
    
    searchInput.addEventListener('input', filterPurchases);
    filterStatus.addEventListener('change', filterPurchases);
  }
  
  // Fechar modais ao clicar fora
  window.addEventListener('click', function(event) {
    const modal = document.getElementById('modal');
    const orderDetailsModal = document.getElementById('order-details-modal');
    
    if (event.target === modal) {
      closeModal();
    }
    
    if (event.target === orderDetailsModal) {
      closeOrderDetailsModal();
    }
  });
  
  // Animar números estatísticos
  const animateStats = () => {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
      const finalValue = stat.textContent;
      stat.textContent = '0';
      
      setTimeout(() => {
        animateNumber(stat, finalValue);
      }, 500);
    });
  };
  
  const animateNumber = (element, finalValue) => {
    const value = parseFloat(finalValue.replace(/[^0-9.-]+/g, ""));
    const prefix = finalValue.replace(/[0-9.-]+/g, "");
    let currentValue = 0;
    const duration = 1500;
    const steps = 30;
    const increment = value / steps;
    
    const interval = setInterval(() => {
      currentValue += increment;
      
      if (currentValue >= value) {
        element.textContent = finalValue;
        clearInterval(interval);
      } else {
        element.textContent = prefix + Math.floor(currentValue).toLocaleString();
      }
    }, duration / steps);
  };
  
  // Iniciar animações
  animateStats();
  
  // Adicionar estilos CSS para notificações
  const style = document.createElement('style');
  style.textContent = `
    .notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      display: flex;
      align-items: center;
      background-color: white;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      z-index: 1200;
      transform: translateY(100px);
      opacity: 0;
      transition: transform 0.3s ease, opacity 0.3s ease;
      max-width: 350px;
    }
    
    .notification.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .notification.success {
      border-left: 4px solid #4CAF50;
    }
    
    .notification.error {
      border-left: 4px solid #F44336;
    }
    
    .notification-icon {
      margin-right: 15px;
      font-size: 24px;
    }
    
    .notification.success .notification-icon {
      color: #4CAF50;
    }
    
    .notification.error .notification-icon {
      color: #F44336;
    }
    
    .notification-message {
      flex: 1;
      font-size: 14px;
    }
    
    .notification-close {
      background: none;
      border: none;
      color: #999;
      cursor: pointer;
      font-size: 16px;
      margin-left: 10px;
      transition: color 0.3s;
    }
    
    .notification-close:hover {
      color: #333;
    }
    
    .loading {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 30px;
      color: #777;
    }
    
    .loading i {
      margin-right: 10px;
    }
    
    .error-message {
      color: #F44336;
      text-align: center;
      padding: 20px;
    }
    
    .order-details {
      padding: 20px 0;
    }
    
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .order-meta {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
      color: #777;
      font-size: 14px;
    }
    
    .order-meta i {
      margin-right: 5px;
      color: var(--primary-color);
    }
    
    .order-items {
      margin: 30px 0;
    }
    
    .order-items h4 {
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
      color: var(--primary-color);
    }
    
    .order-item {
      display: flex;
      padding: 15px;
      border-bottom: 1px solid #eee;
      margin-bottom: 15px;
    }
    
    .item-image {
      width: 80px;
      height: 80px;
      margin-right: 15px;
    }
    
    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 5px;
    }
    
    .item-details {
      flex: 1;
    }
    
    .item-details h5 {
      margin-bottom: 10px;
    }
    
    .item-meta {
      font-size: 14px;
      color: #777;
    }
    
    .item-meta p {
      margin-bottom: 5px;
    }
    
    .item-meta span {
      font-weight: 500;
      color: #555;
    }
    
    .order-summary {
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }
    
    .summary-row.total {
      font-weight: 700;
      font-size: 18px;
      color: var(--primary-color);
      border-bottom: none;
    }
    
    .order-actions {
      display: flex;
      justify-content: flex-end;
    }
    
    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 50px 20px;
      text-align: center;
    }
    
    .empty-state i {
      font-size: 48px;
      color: #ddd;
      margin-bottom: 20px;
    }
    
    .empty-state p {
      color: #777;
      margin-bottom: 20px;
    }
  `;
  document.head.appendChild(style);
});


document.addEventListener('DOMContentLoaded', function() {
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const mobileMenu = document.querySelector('.mobile-menu');

  menuToggle.addEventListener('click', function() {
      mobileMenu.classList.toggle('active');
      
      // Animação do ícone do menu (opcional)
      const spans = menuToggle.querySelectorAll('span');
      if (mobileMenu.classList.contains('active')) {
          spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
          spans[1].style.opacity = '0';
          spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
      } else {
          spans[0].style.transform = 'none';
          spans[1].style.opacity = '1';
          spans[2].style.transform = 'none';
      }
  });
});

// Função para processar o resgate de pontos
function redeemPoints(points, rewardName) {
  if (confirm(`Deseja resgatar "${rewardName}" por ${points} pontos?`)) {
    // Enviar requisição AJAX para processar o resgate
    $.ajax({
      url: 'process_redemption.php',
      type: 'POST',
      data: {
        action: 'redeem_points',
        points: points,
        reward: rewardName
      },
      success: function(response) {
        try {
          const data = JSON.parse(response);
          if (data.status === 'success') {
            alert('Resgate realizado com sucesso! Nossa equipe entrará em contato para providenciar sua recompensa.');
            // Atualizar a página para mostrar os pontos atualizados
            location.reload();
          } else {
            alert('Erro ao resgatar pontos: ' + data.message);
          }
        } catch (e) {
          alert('Ocorreu um erro ao processar sua solicitação.');
          console.error(e);
        }
      },
      error: function() {
        alert('Ocorreu um erro ao conectar com o servidor.');
      }
    });
  }
}

// Função para editar uma encomenda
function editOrder(orderId) {
  if (confirm('Deseja editar esta encomenda?')) {
    window.location.href = 'edit-order.php?id=' + orderId;
  }
}

// Função para cancelar pedido
