function mascaraData(input) {
    let v = input.value.replace(/\D/g, ""); // Remove não-números
    v = v.replace(/(\d{2})(\d)/, "$1/$2"); // Barra após o dia
    v = v.replace(/(\d{2})(\d)/, "$1/$2"); // Barra após o mês
    input.value = v;
}

async function verificarNome(input) {

    const valor = input.value;

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-nome').classList.remove('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-nome').classList.add('hidden');
        return true;
    }
}

async function verificarPatins(input) {

    const valor = input.value;

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-patins').classList.remove('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-patins').classList.add('hidden');
        return true;
    }
}

async function verificarResponsavel(input) {

    const valor = input.value;

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-titular').classList.remove('hidden');

    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-titular').classList.add('hidden');
        return true;
    }
}

// Valida se a data existe e se o usuário não é "do futuro"
function validarData(input) {
    const dataString = input.value;

    if (dataString.length !== 10) return;

    const [dia, mes, ano] = dataString.split('/').map(Number);
    const data = new Date(ano, mes - 1, dia);
    const hoje = new Date();

    // Verifica se a data é válida e se não é maior que hoje
    const dataValida = data.getFullYear() === ano &&
        data.getMonth() === mes - 1 &&
        data.getDate() === dia &&
        data <= hoje;

    if (!dataValida) return false;
    return true;
}

async function verificarNascimento(input) {

    const valor = input.value.replace(/\D/g, '');

    let valido = false;

    valido = validarData(input);

    if (!valido) {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        if (valor !== "") {
            // Mostra a mensagem
            document.getElementById('erro-nascimentoVazio').classList.add('hidden');
            document.getElementById('erro-nascimento').classList.remove('hidden');
        } else {
            // Mostra a mensagem
            document.getElementById('erro-nascimento').classList.add('hidden');
            document.getElementById('erro-nascimentoVazio').classList.remove('hidden');
        }
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-nascimentoVazio').classList.add('hidden');
        document.getElementById('erro-nascimento').classList.add('hidden');
        return true;
    }

}

document.getElementById('atleta').addEventListener('submit', async function (event) {

    // Impede o formulário de ser enviado antes da validação
    event.preventDefault();

    const btn = document.getElementById('btnEnviar');
    const spinner = document.getElementById('spinner');
    const texto = document.getElementById('textoBotao');

    let valorResponsavel = document.getElementById('titular');
    let titular = await verificarResponsavel(valorResponsavel);

    let valorNome = document.getElementById('nome');
    let nome = await verificarNome(valorNome);

    let valorNascimento = document.getElementById('nascimento');
    let nascimento = await verificarNascimento(valorNascimento);

    let valorPatins = document.getElementById('patins');
    let patins = await verificarPatins(valorPatins);

    // Aqui você envia os dados usando fetch()

    try {
        if (!titular || !nome || !nascimento || !patins) {
            alert('o formulário contém 1 ou mais erros, confira os avisos em vermelho abaixo dos campos ');
            return;
        } else {
            // SE CHEGOU AQUI, TUDO ESTÁ OK!
            this.submit();
        }

    } catch (error) {
        console.error("Erro técnico na validação:", error);
    }

    // ATIVAR LOADING
    btn.disabled = true;                // Desativa o botão para evitar cliques duplos
    spinner.classList.remove('hidden'); // Mostra o spinner
    texto.innerText = "Enviando...";    // Muda o texto

    // Simulando o tempo de resposta do servidor (ex: 2 segundos)
    setTimeout(() => {
        alert("Dados enviados com sucesso!");

        // RESETAR BOTÃO (Após o sucesso ou erro)
        btn.disabled = false;
        spinner.classList.add('hidden');
        texto.innerText = "Cadastrar";
    }, 2000);
});