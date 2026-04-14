    document.addEventListener('DOMContentLoaded', () => {
      const container = document.getElementById('containerMenu');
      const checkbox = document.getElementById('botaoMenu');
      let tempoParaFechar;

      if (container && checkbox) {
        // Ao SAIR do menu completo
        container.addEventListener('mouseleave', () => {
          // Espera 80ms. Se o mouse não voltar, ele fecha.
          tempoParaFechar = setTimeout(() => {
            checkbox.checked = false;
          }, 80);
        });

        // Ao VOLTAR para o menu (cancela o fechamento)
        container.addEventListener('mouseenter', () => {
          clearTimeout(tempoParaFechar);
        });

        // Clique fora continua funcionando normalmente
        document.addEventListener('click', (e) => {
          if (!container.contains(e.target)) {
            checkbox.checked = false;
          }
        });
      }
    });