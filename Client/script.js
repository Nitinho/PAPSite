// Existing modal functionality
function showModal(type) {
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const modalFields = document.getElementById('modal-fields');
    const modalForm = document.getElementById('modal-form');
    
    // Clear previous fields
    modalFields.innerHTML = '';
    
    // Configure modal based on type
    switch(type) {
      case 'password':
        modalTitle.textContent = 'Mudar Senha';
        modalFields.innerHTML = `
          <input type="password" placeholder="Senha atual" required>
          <input type="password" placeholder="Nova senha" required>
          <input type="password" placeholder="Confirmar nova senha" required>
        `;
        break;
      case 'email':
        modalTitle.textContent = 'Alterar Email';
        modalFields.innerHTML = `
          <input type="email" placeholder="Email atual" required>
          <input type="email" placeholder="Novo email" required>
          <input type="password" placeholder="Confirmar senha" required>
        `;
        break;
      case 'name':
        modalTitle.textContent = 'Alterar Nome';
        modalFields.innerHTML = `
          <input type="text" placeholder="Novo nome" required>
        `;
        break;
      case 'address':
        modalTitle.textContent = 'Adicionar Morada';
        modalFields.innerHTML = `
          <input type="text" placeholder="Rua" required>
          <input type="text" placeholder="Número" required>
          <input type="text" placeholder="Complemento">
          <input type="text" placeholder="Cidade" required>
          <input type="text" placeholder="Código Postal" required>
        `;
        break;
      case 'phone':
        modalTitle.textContent = 'Telefone';
        modalFields.innerHTML = `
          <input type="tel" placeholder="Novo número de telefone" required>
        `;
        break;
    }
    
    modal.style.display = 'block';
    
    modalForm.onsubmit = (e) => {
      e.preventDefault();
      // Here you would add logic to process the form
      alert('Alterações salvas com sucesso!');
      closeModal();
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