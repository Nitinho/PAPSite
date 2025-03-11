

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


    /* JavaScript para ativação de elementos */
    document.addEventListener('DOMContentLoaded', function() {
        // Ativar menu mobile
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                
                const spans = menuToggle.querySelectorAll('span');
                spans[0].classList.toggle('rotate-down');
                spans[1].classList.toggle('fade-out');
                spans[2].classList.toggle('rotate-up');
            });
        }
        
        // Ativar animações de fade-in
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        
        fadeElements.forEach(element => {
            observer.observe(element);
        });
        
        // Ativar perguntas frequentes
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            item.addEventListener('click', function() {
                this.classList.toggle('active');
                
                const answer = this.querySelector('.faq-answer');
                if (this.classList.contains('active')) {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    answer.style.paddingTop = '15px';
                } else {
                    answer.style.maxHeight = 0;
                    answer.style.paddingTop = 0;
                }
            });
        });
        
        // Função para copiar para área de transferência
        window.copyToClipboard = function(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copiado para a área de transferência!');
            }).catch(err => {
                console.error('Erro ao copiar: ', err);
            });
        };
    });
    