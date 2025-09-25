// Localize o modal e os botões
const modal = document.getElementById('avisoModal');
const closeButton = document.querySelector('.close-button');
const okButton = document.querySelector('.ok-button');

// Função para exibir o modal
function showModal() {
  modal.style.display = 'flex';
}

// Função para esconder o modal
function hideModal() {
  modal.style.display = 'none';
}

// Eventos para o modal (verificando se os elementos existem)
if (closeButton) {
  closeButton.addEventListener('click', hideModal);
}
if (okButton) {
  okButton.addEventListener('click', hideModal);
}

// Fechar o modal clicando fora dele
window.addEventListener('click', (event) => {
  if (event.target === modal) {
    hideModal();
  }
});

// Ações executadas após o carregamento do DOM
document.addEventListener('DOMContentLoaded', () => {
  const logoutLink = document.querySelector('a[href="logout.php"]');
  const menuToggle = document.querySelector('.menu-toggle');
  const menuLinks = document.querySelector('.menu-links');
  const header = document.querySelector('.header');

  // Ações para o link de logout
  if (logoutLink) {
    logoutLink.addEventListener('click', (event) => {
      // Verifica se a variável de sessão 'caixa_id' existe
      const isCaixaAberto = document.body.classList.contains('conferencia-aberta');
      if (isCaixaAberto) {
        event.preventDefault(); // Impede a navegação
        showModal(); // Mostra o modal de aviso
      }
    });
  }

  // Código para o Menu Hambúrguer (verificando se os elementos existem)
  if (menuToggle && menuLinks && header) {
    menuToggle.addEventListener('click', () => {
      menuLinks.classList.toggle('active');
      const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', !isExpanded);
    });
  }
});