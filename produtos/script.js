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
    const confirmationModal = document.getElementById('confirmation-modal');
    const successModal = document.getElementById('success-modal');
    const closeButtons = document.querySelectorAll('.close');
    const confirmOrderBtn = document.getElementById('confirm-order');
    const cancelOrderBtn = document.getElementById('cancel-order');
    const continueShoppingBtn = document.getElementById('continue-shopping');
    const modalCartItems = document.getElementById('modal-cart-items');
    const modalTotalValue = document.getElementById('modal-total-value');
    const emptyCart = document.querySelector('.empty-cart');
    
    // Carrinho de compras
    let cart = [];
    
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
                    <small>Adicione produtos para continuar</small>
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
                            <span>x${item.quantity}</span>
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
            // Inicializar a exibição do carrinho após carregar do localStorage
            setTimeout(() => {
                updateCartDisplay();
            }, 100);
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
                    <p>${item.quantity} x € ${(item.price).toFixed(2).replace('.', ',')} = € ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</p>
                </div>
            `;
            modalCartItems.appendChild(modalItem);
        });
        
        // Atualizar o total no modal
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        modalTotalValue.textContent = `€ ${total.toFixed(2).replace('.', ',')}`;
        
        // Mostrar o modal
        openModal(confirmationModal);
    }
    
    // Abrir modal
    function openModal(modal) {
        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
    
    // Fechar modal
    function closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Ordenar produtos
    const sortBySelect = document.getElementById('sort-by');
    if (sortBySelect) {
        sortBySelect.addEventListener('change', function() {
            const value = this.value;
            const products = Array.from(document.querySelectorAll('.product'));
            const container = document.querySelector('.product-container');
            
            products.sort((a, b) => {
                if (value === 'name-asc') {
                    return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                } else if (value === 'name-desc') {
                    return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                } else if (value === 'price-asc') {
                    return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
                } else if (value === 'price-desc') {
                    return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
                }
            });
            
            // Limpar container
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }
            
            // Adicionar produtos ordenados
            products.forEach(product => {
                container.appendChild(product);
            });
        });
    }
    
    // Pesquisar produtos
    const searchBtn = document.getElementById('search-btn');
    const searchInput = document.getElementById('search-input');
    
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', function() {
            const searchTerm = searchInput.value.toLowerCase();
            const products = document.querySelectorAll('.product');
            
            products.forEach(product => {
                const name = product.getAttribute('data-name').toLowerCase();
                
                if (name.includes(searchTerm) || searchTerm === '') {
                    product.style.display = 'flex';
                } else {
                    product.style.display = 'none';
                }
            });
        });
        
        // Pesquisar ao pressionar Enter
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }
    
    // Botão de Voltar ao Topo
    const backToTopButton = document.getElementById('back-to-top');

    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Adicionar CSS para notificação e modal
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #FF4C4C;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .modal {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
        }
        
        .modal-content {
            transform: translateY(-50px);
            transition: transform 0.3s ease;
        }
        
        .modal.show .modal-content {
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
    
    // Fechar modais
    closeButtons.forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
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
                    // Fechar o modal de confirmação
                    closeModal(confirmationModal);
                    
                    
                    // Mostrar modal de sucesso
                    setTimeout(() => {
                        openModal(successModal);
                        // Limpar o carrinho
                        clearCart();
                    }, 500);
                    
                    // Redirecionar para a página de confirmação se necessário
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 3000);
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
        closeModal(confirmationModal);
    });
    
    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', function() {
            closeModal(successModal);
        });
    }
    
    // Fechar o modal ao clicar fora dele
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target);
        }
    });
    
    // Inicializar a exibição do carrinho
    updateCartDisplay();
});


