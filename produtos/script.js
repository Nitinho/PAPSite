document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const btnPlus = document.querySelectorAll('.btn-plus');
    const btnMinus = document.querySelectorAll('.btn-minus');
    const btnOrder = document.querySelectorAll('.btn-order');
    const cartItems = document.querySelector('.cart-items');
    const cartTotalValue = document.getElementById('cart-total-value');
    const checkoutBtn = document.getElementById('checkout-btn');
    const clearCartBtn = document.getElementById('clear-cart');
    const modal = document.getElementById('confirmation-modal');
    const closeModal = document.querySelector('.close');
    const confirmOrderBtn = document.getElementById('confirm-order');
    const cancelOrderBtn = document.getElementById('cancel-order');
    const modalCartItems = document.getElementById('modal-cart-items');
    const modalTotalValue = document.getElementById('modal-total-value');
    
    // Carrinho de compras
    let cart = [];
    
    // Carregar carrinho do localStorage
    loadCartFromLocalStorage();
    
    // Funções
    function updateQuantity(id, value) {
        const input = document.getElementById(`qty-${id}`);
        let qty = parseInt(input.value) + value;
        
        if (qty < 1) qty = 1;
        input.value = qty;
    }
    
    function addToCart(id, name, price, image, quantity) {
        // Verificar se o produto já está no carrinho
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            // Atualizar quantidade se já existe
            existingItem.quantity += quantity;
        } else {
            // Adicionar novo item ao carrinho
            cart.push({
                id: id,
                name: name,
                price: price,
                image: image,
                quantity: quantity
            });
        }
        
        // Salvar carrinho no localStorage
        saveCartToLocalStorage();
        
        // Atualizar exibição do carrinho
        updateCartDisplay();
        
        // Mostrar notificação
        showNotification(`${name} adicionado ao carrinho!`);
    }
    
    function updateCartDisplay() {
        // Limpar o conteúdo atual do carrinho
        cartItems.innerHTML = '';
        
        if (cart.length === 0) {
            // Mostrar mensagem de carrinho vazio
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Seu carrinho está vazio</p>
                </div>
            `;
            checkoutBtn.disabled = true;
        } else {
            // Adicionar itens ao carrinho
            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">€ ${(item.price).toFixed(2).replace('.', ',')}</div>
                        <div class="cart-item-quantity">
                            Qtd: ${item.quantity} × € ${(item.price).toFixed(2).replace('.', ',')}
                        </div>
                    </div>
                    <button class="cart-item-remove" data-id="${item.id}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                cartItems.appendChild(cartItem);
            });
            
            // Habilitar botão de checkout
            checkoutBtn.disabled = false;
        }
        
        // Atualizar total
        updateCartTotal();
        
        // Adicionar event listeners para os botões de remover
        document.querySelectorAll('.cart-item-remove').forEach(button => {
            button.addEventListener('click', function() {
                removeFromCart(this.dataset.id);
            });
        });
    }
    
    function removeFromCart(id) {
        // Encontrar o índice do item no carrinho
        const index = cart.findIndex(item => item.id === id);
        
        if (index !== -1) {
            // Remover o item do carrinho
            const removedItem = cart.splice(index, 1)[0];
            
            // Salvar carrinho no localStorage
            saveCartToLocalStorage();
            
            // Atualizar exibição do carrinho
            updateCartDisplay();
            
            // Mostrar notificação
            showNotification(`${removedItem.name} removido do carrinho!`);
        }
    }
    
    function updateCartTotal() {
        // Calcular o total do carrinho
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Atualizar o elemento de total
        cartTotalValue.textContent = `€ ${total.toFixed(2).replace('.', ',')}`;
    }
    
    function clearCart() {
        // Limpar o carrinho
        cart = [];
        
        // Salvar carrinho no localStorage (vazio)
        saveCartToLocalStorage();
        
        // Atualizar exibição do carrinho
        updateCartDisplay();
        
        // Mostrar notificação
        showNotification('Carrinho limpo!');
    }
    
    function saveCartToLocalStorage() {
        localStorage.setItem('shoppingCart', JSON.stringify(cart));
    }
    
    function loadCartFromLocalStorage() {
        const savedCart = localStorage.getItem('shoppingCart');
        if (savedCart) {
            cart = JSON.parse(savedCart);
            // Não chame updateCartDisplay() aqui, pois os elementos DOM podem não estar prontos
        }
    }
    
    function showNotification(message) {
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        // Adicionar ao body
        document.body.appendChild(notification);
        
        // Mostrar notificação
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Remover notificação após 3 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    function showCheckoutModal() {
        // Preencher o modal com os itens do carrinho
        modalCartItems.innerHTML = '';
        
        cart.forEach(item => {
            const modalItem = document.createElement('div');
            modalItem.className = 'modal-item';
            modalItem.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="modal-item-details">
                    <h4>${item.name}</h4>
                    <p>Quantidade: ${item.quantity}</p>
                    <p>Preço unitário: € ${(item.price).toFixed(2).replace('.', ',')}</p>
                    <p>Subtotal: € ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</p>
                </div>
            `;
            modalCartItems.appendChild(modalItem);
        });
        
        // Atualizar o total no modal
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        modalTotalValue.textContent = `€ ${total.toFixed(2).replace('.', ',')}`;
        
        // Mostrar o modal
        modal.style.display = 'block';
    }

    // Adicionar CSS para notificação
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #FF4C4C;
            color: white;
            padding: 12px 18px;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);
    
    // Event Listeners
    btnPlus.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            updateQuantity(id, 1);
        });
    });
    
    btnMinus.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            updateQuantity(id, -1);
        });
    });
    
    btnOrder.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            const quantity = parseInt(document.getElementById(`qty-${id}`).value);
            
            addToCart(id, name, price, image, quantity);
        });
    });
    
    clearCartBtn.addEventListener('click', clearCart);
    
    checkoutBtn.addEventListener('click', showCheckoutModal);
    
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    confirmOrderBtn.addEventListener('click', function() {
        // Preparar os dados do carrinho para envio
        const orderData = {
            items: cart,
            total: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        };
        
        // Enviar os dados para o servidor usando fetch
        fetch('../process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    // Limpar o carrinho após a confirmação
                    clearCart();
                    
                    // Fechar o modal
                    modal.style.display = 'none';
                    
                    // Mostrar notificação de confirmação
                    showNotification('Pedido confirmado com sucesso!');
                    
                    // Redirecionar para a página de confirmação se necessário
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    // Mostrar mensagem de erro
                    showNotification('Erro: ' + data.message);
                }
            } catch (e) {
                console.error('Erro ao processar resposta:', text);
                showNotification('Erro ao processar o pedido. Verifique o console.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao processar o pedido. Tente novamente.');
        });
    });
    
    cancelOrderBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Fechar o modal ao clicar fora dele
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Inicializar a exibição do carrinho
    updateCartDisplay();
});
