document.addEventListener("DOMContentLoaded", function () {
    const cartCount = document.getElementById("cart-count");
    const orderButtons = document.querySelectorAll(".btn-order");
    const cartModal = document.getElementById("cart-modal");
    const cartItems = document.getElementById("cart-items");
    const confirmOrderButton = document.getElementById("confirm-order");
    const clearCartButton = document.getElementById("clear-cart");
    const cartContainer = document.getElementById("cart-container");
    const closeBtn = document.getElementsByClassName("close")[0];
    const cartTotal = document.getElementById("cart-total");

    let cart = [];

    orderButtons.forEach(button => {
        button.addEventListener("click", function () {
            const product = this.closest('.product');
            const productName = product.querySelector('h4').textContent;
            const quantity = parseInt(product.querySelector('.quantity-input').value);
            const productImage = product.querySelector('img').src;
            const price = productName === 'Pão' ? 0.50 : 1.00; // Exemplo de preço

            addToCart(productName, quantity, productImage, price);
            updateCartCount();
        });
    });

    function addToCart(name, quantity, image, price) {
        const existingItem = cart.find(item => item.name === name);
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({ name, quantity, image, price });
        }
    }

    function updateCartCount() {
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
    }

    function displayCart() {
        cartItems.innerHTML = '';
        let total = 0;
        cart.forEach(item => {
            const itemTotal = item.quantity * item.price;
            total += itemTotal;
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <p>Quantidade: ${item.quantity}</p>
                    <p>Preço: R$ ${item.price.toFixed(2)}</p>
                    <p>Total: R$ ${itemTotal.toFixed(2)}</p>
                </div>
                <button class="remove-item" data-name="${item.name}">Remover</button>
            `;
            cartItems.appendChild(itemElement);
        });

        cartTotal.textContent = total.toFixed(2);

        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const nameToRemove = this.getAttribute('data-name');
                cart = cart.filter(item => item.name !== nameToRemove);
                updateCartCount();
                displayCart();
            });
        });
    }

    cartContainer.addEventListener("click", function () {
        displayCart();
        cartModal.style.display = "block";
    });

    closeBtn.onclick = function() {
        cartModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == cartModal) {
            cartModal.style.display = "none";
        }
    }

    confirmOrderButton.addEventListener('click', function() {
        // Aqui você pode adicionar a lógica para enviar o pedido para o servidor
        alert('Pedido confirmado! Total: R$ ' + cartTotal.textContent);
        cart = [];
        updateCartCount();
        cartModal.style.display = "none";
    });

    clearCartButton.addEventListener('click', function() {
        cart = [];
        updateCartCount();
        displayCart();
    });
});
