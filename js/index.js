

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

document.addEventListener('DOMContentLoaded', function () {
    // Adiciona o evento de clique a cada pergunta
    document.querySelectorAll('.faq-question').forEach((question) => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;

            // Fecha outras respostas abertas
            document.querySelectorAll('.faq-answer').forEach((ans) => {
                if (ans !== answer) {
                    ans.style.maxHeight = null;
                }
            });

            // Alterna entre abrir e fechar a resposta clicada
            if (answer.style.maxHeight) {
                answer.style.maxHeight = null; // Fecha se já estiver aberta
            } else {
                answer.style.maxHeight = answer.scrollHeight + "px"; // Abre a resposta
            }
        });
    });
});



function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert("Copiado para a área de transferência: " + text);
    }).catch(err => {
        alert("Erro ao copiar: " + err);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');

    menuToggle.addEventListener('click', function() {
        this.classList.toggle('active');
        mobileMenu.classList.toggle('active');
    });

    // Fechar o menu ao clicar em um link
    const mobileMenuLinks = mobileMenu.getElementsByTagName('a');
    for (let link of mobileMenuLinks) {
        link.addEventListener('click', function() {
            menuToggle.classList.remove('active');
            mobileMenu.classList.remove('active');
        });
    }
});
