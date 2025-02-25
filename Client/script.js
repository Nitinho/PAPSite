function showModal(type) {
  const modal = document.getElementById('modal');
  const modalTitle = document.getElementById('modal-title');
  const modalFields = document.getElementById('modal-fields');
  const modalForm = document.getElementById('modal-form');

  // Clear previous fields
  modalFields.innerHTML = '';

  // Configure modal based on type
  switch (type) {
    case 'password':
      modalTitle.textContent = 'Mudar Senha';
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
      modalTitle.textContent = 'Telefone';
      modalFields.innerHTML = `
                <input type="tel" name="phone" placeholder="Novo número de telefone" required>
                <input type="hidden" name="action" value="add_phone">
            `;
      break;
  }

  modal.style.display = 'block';

  modalForm.onsubmit = (e) => {
    e.preventDefault();
    const formData = new FormData(modalForm);
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
          alert(data.message);
          closeModal();
          location.reload();
        } else {
          throw new Error(data.message);
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro: ' + error.message);
      });
  };
}


function closeModal() {
  const modal = document.getElementById('modal');
  modal.style.display = 'none';
}

// Close modal when clicking outside
window.onclick = (event) => {
  const modal = document.getElementById('modal');
  if (event.target === modal) {
    closeModal();
  }
}

// Animate stats on page load
document.addEventListener('DOMContentLoaded', () => {
  const statNumbers = document.querySelectorAll('.stat-number');
  statNumbers.forEach(stat => {
    const finalValue = stat.textContent;
    stat.textContent = '0';
    setTimeout(() => {
      animateNumber(stat, finalValue);
    }, 500);
  });
});

function animateNumber(element, finalValue) {
  const value = parseFloat(finalValue.replace(/[^0-9.-]+/g, ""));
  const prefix = finalValue.replace(/[0-9.-]+/g, "");
  let currentValue = 0;
  const duration = 1000;
  const steps = 20;
  const increment = value / steps;

  const interval = setInterval(() => {
    currentValue += increment;
    if (currentValue >= value) {
      element.textContent = finalValue;
      clearInterval(interval);
    } else {
      element.textContent = prefix + Math.floor(currentValue);
    }
  }, duration / steps);
}