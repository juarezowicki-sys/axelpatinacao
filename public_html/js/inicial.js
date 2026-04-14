
document.addEventListener('click', function (event) {
    const checkbox = document.getElementById('botaoMenu');
    const containerMenu = document.getElementById('divMenu'); // O container que envolve tudo

    // Se o menu estiver aberto...
    if (checkbox.checked) {
        // ...e o clique NÃO foi dentro do container (botão + menu)
        if (!containerMenu.contains(event.target)) {
            fecharDiv();
        }
    }
});

const navElement = document.getElementById('menu'); 
navElement.addEventListener('mouseleave', () => {
  fecharDiv();
});

function fecharDiv() {
  console.log("Nav fechada!");
  const btnMenu = document.getElementById('botaoMenu');
  // Inverte o estado 'hidden' apenas do menu relacionado a este botão
  btnMenu.checked = false;
}