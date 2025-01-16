
document.addEventListener("DOMContentLoaded", function () {
    const cartCount = document.getElementById("cart-count");
    const orderButtons = document.querySelectorAll(".btn-order");

    let itemCount = 0;

    orderButtons.forEach(button => {
        button.addEventListener("click", function () {
            const quantityInput = this.parentNode.querySelector(".quantity-input");
            const quantity = parseInt(quantityInput.value);

            if (!isNaN(quantity) && quantity > 0) {
                itemCount += quantity;
                cartCount.textContent = itemCount;
            }
        });
    });

    // Adicionar funcionalidade de abrir o carrinho ao clicar
    const cartContainer = document.getElementById("cart-container");
    cartContainer.addEventListener("click", function () {
        alert("Abrir o carrinho (futuro desenvolvimento).");
    });
});

document.addEventListener("DOMContentLoaded", () => {
    // Selecionar todos os botões de adição e subtração
    const minusButtons = document.querySelectorAll(".btn-minus");
    const plusButtons = document.querySelectorAll(".btn-plus");

    // Lógica para os botões de subtração
    minusButtons.forEach((btn) => {
        btn.addEventListener("click", (event) => {
            const quantityInput = event.target.closest(".quantity").querySelector(".quantity-input");
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
    });

    // Lógica para os botões de adição
    plusButtons.forEach((btn) => {
        btn.addEventListener("click", (event) => {
            const quantityInput = event.target.closest(".quantity").querySelector(".quantity-input");
            let currentValue = parseInt(quantityInput.value);
            quantityInput.value = currentValue + 1;
        });
    });
});
