
        const frame = document.getElementById('meuFrame');
        // Adicione isso logo após definir a constante 'frame'
        frame.onload = function() { esconderSpinner(); };
        const loader = document.getElementById('loader');
        const modal = document.getElementById('modalFrame');
        const containerConteudo = document.getElementById('containerConteudo');

        function esconderSpinner() {
            // Verifica se não é a página em branco inicial
            if (frame.src !== "about:blank") {
                loader.classList.add('hidden');
            }
        }

        function carregar() {
            // 2. Define a URL (Troque pela URL desejada)        
            if (frame.src === "about:blank") {
                containerConteudo.classList.remove('hidden');
                loader.classList.remove('hidden'); // Mostra o fundo/Spinner
                modal.classList.remove('hidden'); // Mostra o modal o modal 
                frame.src = "https://axelpatinacao.basedeclientes.com.br";
            } else {
                fecharFrame();
            }
        }

        window.addEventListener('mousedown', function(event) {
            const modal = document.getElementById('modalFrame'); // O fundo escuro/overlay
            const containerConteudo = document.getElementById('containerConteudo'); // O div branco que envolve o iframe
            const frame = document.getElementById('meuFrame');
            const botao = document.getElementById('botaoAbrir'); // Pega o botão pelo ID

            // Só agimos se o modal estiver aberto (não estiver escondido)
            if (!modal.classList.contains('hidden')) {

                // Se o clique NÃO foi no container, NEM no iframe, NEM no botão de abrir
                if (!containerConteudo.contains(event.target) &&
                    event.target !== frame &&
                    event.target !== botao) {
                    fecharFrame();
                }
            }
        });

        function fecharFrame() {
            const modal = document.getElementById('modalFrame');
            const frame = document.getElementById('meuFrame');
            frame.src = "about:blank";
            modal.classList.add('hidden');
        }