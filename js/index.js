

const images = [
    "img/polvo.jpg",
    "img/bacalhau.jpg"
];

let currentIndex = 0;

function changeImage() {
    // Obtém o elemento da imagem pelo ID
    const imgElement = document.getElementById('imgcontainer3');
    // Alterna o índice da imagem
    currentIndex = (currentIndex + 1) % images.length;
    // Define o novo caminho da imagem
    imgElement.src = images[currentIndex];
}

// Alterna a imagem a cada 10 segundos
setInterval(changeImage, 10000);

// Função para abrir um novo HTML em uma nova aba ao clicar na imagem
function openNewPage() {
    // Aqui você pode redirecionar para diferentes páginas com base na imagem atual
    if (currentIndex === 0) {
        window.open("receitas/polvo.html", "_blank"); // Abre polvo.html em uma nova aba
    } else if (currentIndex === 1) {
        window.open("receitas/bacalhau.html", "_blank"); // Abre bacalhau.html em uma nova aba
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const fadeElements = document.querySelectorAll('.fade-in');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            } else {
                entry.target.classList.remove('visible'); // Se quiser que o efeito reverta ao rolar para cima
            }
        });
    });

    fadeElements.forEach(element => {
        observer.observe(element);
    });
});