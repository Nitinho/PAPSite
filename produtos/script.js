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
                <span>${item.name}</span>
                <span>Quantidade: ${item.quantity}</span>
                <span>Preço: R$ ${item.price.toFixed(2)}</span>
                <span>Total: R$ ${itemTotal.toFixed(2)}</span>
            `;
            cartItems.appendChild(itemElement);
        });
        cartTotal.textContent = `Total: R$ ${total.toFixed(2)}`;
    }

    confirmOrderButton.addEventListener('click', function () {
        if (cart.length === 0) {
            alert('Carrinho vazio!');
            return;
        }

        const data = cart.map(item => ({ id: item.id, quantity: item.quantity, price: item.price }));
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao processar a requisição');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Limpar o carrinho
                cart = [];
                updateCartCount();
                displayCart();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erro:', error));
    });

    displayCart();
});
