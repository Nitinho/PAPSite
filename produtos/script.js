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
            const productId = this.getAttribute('data-id');
            const productName = product.querySelector('h4').textContent;
            const quantity = parseInt(product.querySelector('.quantity-input').value);
            const productImage = product.querySelector('img').src;
            const price = parseFloat(this.getAttribute('data-price'));

            addToCart(productId, productName, quantity, productImage, price);
            updateCartCount();
        });
    });

    function addToCart(id, name, quantity, image, price) {
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({ id, name, quantity, image, price });
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
                <button class="remove-item" data-id="${item.id}">Remover</button>
            `;
            cartItems.appendChild(itemElement);
        });

        cartTotal.textContent = total.toFixed(2);

        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const idToRemove = this.getAttribute('data-id');
                cart = cart.filter(item => item.id !== idToRemove);
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
        const cartData = JSON.stringify(cart);
        
        fetch('../process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: cartData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pedido confirmado! Número do pedido: ' + data.orderId);
                cart = [];
                updateCartCount();
                displayCart();
                cartModal.style.display = "none";
            } else {
                alert('Erro ao processar o pedido: ' + data.message);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Erro ao processar o pedido.');
        });
    });

    clearCartButton.addEventListener('click', function() {
        cart = [];
        updateCartCount();
        displayCart();
    });
});
