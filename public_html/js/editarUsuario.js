// Máscara para documento "000.000.000-00 ou 00.000.000/0000-00" (cnpj e cpf)
function mascaraDocumento(input) {

    let v = input.value.replace(/\D/g, ""); // Remove tudo que não é número

    if (v.length <= 11) {
        // Máscara de CPF: 000.000.000-00
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    } else {
        // Máscara de CNPJ: 00.000.000/0000-00
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
    }
    input.value = v;
}

function mascaraTelefone(input) {
    let v = input.value.replace(/\D/g, ""); // Remove não-números
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); // Adiciona parênteses no DDD
    v = v.replace(/(\d)(\d{4})$/, "$1-$2");    // Adiciona o hífen antes dos últimos 4 dígitos
    input.value = v;
}



function mascaraCEP(input) {
    let v = input.value.replace(/\D/g, ""); // Remove tudo o que não é dígito
    v = v.replace(/^(\d{5})(\d)/, "$1-$2"); // Adiciona o hífen após o 5º dígito
    input.value = v;
}


async function verificarTelefone(input) {

    const valor = input.value.replace(/\D/g, '');

    let valido = false;
    if (valor.length === 11 || valor.length === 10) { valido = true; }

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-foneVazio').classList.remove('hidden');
        document.getElementById('erro-telefone').classList.add('hidden');
        return null;
    }
    else if (!valido || valor === "0000000000" || valor === "00000000000") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-telefone').classList.remove('hidden');
        document.getElementById('erro-foneVazio').classList.add('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-telefone').classList.add('hidden');
        document.getElementById('erro-foneVazio').classList.add('hidden');
        return true;
    }

}

async function verificarEmail(input) {

    const valor = input.value;

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-emailVazio').classList.remove('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-emailVazio').classList.add('hidden');
        return true;
    }

}

// inicio pesquisacep
function limpa_formulário_cep() {
    //Limpa valores do formulário de cep.
    document.getElementById('logradouro').value = ("");
    document.getElementById('bairro').value = ("");
    document.getElementById('localidade').value = ("");
    document.getElementById('uf').value = ("");
    //document.getElementById('ibge').value = ("");
}
function meu_callback(conteudo) {

    if (!("erro" in conteudo)) {
        //Atualiza os campos com os valores.
        document.getElementById('logradouro').value = (conteudo.logradouro);
        document.getElementById('bairro').value = (conteudo.bairro);
        document.getElementById('localidade').value = (conteudo.localidade);
        document.getElementById('uf').value = (conteudo.uf);
        //document.getElementById('ibge').value = (conteudo.ibge);
    } //end if.
    else {
        //CEP não Encontrado.

        limpa_formulário_cep();
        document.getElementById('erro-CEP').classList.add('hidden');
        document.getElementById('erro-cepVazio').classList.add('hidden');
        document.getElementById('erro-cepNaoEnc').classList.remove('hidden');
    }

}
async function pesquisacep(valor) {

    //Nova variável "cep" somente com dígitos.
    const cep = valor;

    //Verifica se campo cep possui valor informado.
    if (cep != "") {
        //Expressão regular para validar o CEP.
        var validacep = /^[0-9]{8}$/;

        //Valida o formato do CEP.
        if (validacep.test(cep)) {

            //Preenche os campos com "..." enquanto consulta webservice.
            document.getElementById('logradouro').value = "...";
            document.getElementById('bairro').value = "...";
            document.getElementById('localidade').value = "...";
            document.getElementById('uf').value = "...";
            //document.getElementById('ibge').value="...";

            //Cria um elemento javascript.
            const script = document.createElement('script');

            //Sincroniza com o callback.
            script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

            //Insere script no documento e carrega o conteúdo.
            document.body.appendChild(script);


        } //end if.
        else {
            //cep é inválido.
            limpa_formulário_cep();
            return "cepInvalido";
        }
    } //end if.
    else {
        //cep sem valor, limpa formulário.
        limpa_formulário_cep();
        return 'cepVazio';
    }
};
// final pesquisacep
async function verificarCep(input) {

    const valor = input.value.replace(/\D/g, '');

    let valido = await pesquisacep(valor);

    if (valido === 'cepInvalido' && valor !== "") {
        document.getElementById('erro-CEP').classList.remove('hidden');
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-cepVazio').classList.add('hidden');
        document.getElementById('erro-cepNaoEnc').classList.add('hidden');
        document.getElementById('erro-CEP').classList.remove('hidden');
        return null;
    } else if (valido === 'cepVazio' && valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-cepNaoEnc').classList.add('hidden');
        document.getElementById('erro-CEP').classList.add('hidden');
        document.getElementById('erro-cepVazio').classList.remove('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-CEP').classList.add('hidden');
        document.getElementById('erro-cepVazio').classList.add('hidden');
        document.getElementById('erro-cepNaoEnc').classList.add('hidden');
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

async function verificarComplementos(input) {

    const valor = input.value;

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-complementos').classList.remove('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-complementos').classList.add('hidden');
        return true;

    }

}

async function verificarConfirm(input) {

    const valor = input.value;

    if (valor === "") {
        // Estilo de erro no Input
        input.classList.add('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        // Mostra a mensagem
        document.getElementById('erro-confirm').classList.remove('hidden');
        return null;
    } else {
        // Remove estilos de erro
        input.classList.remove('border-red-500', 'ring-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        // Esconde a mensagem
        document.getElementById('erro-confirm').classList.add('hidden');
        return true;

    }

}

document.getElementById('usuario').addEventListener('submit', async function (event) {

    // Impede o formulário de ser enviado antes da validação
    event.preventDefault();

    const btn = document.getElementById('btnEnviar');
    const spinner = document.getElementById('spinner');
    const texto = document.getElementById('textoBotao');


    let valorFone = document.getElementById('telefone');
    let telefone = await verificarTelefone(valorFone);

    let valorEmail = document.getElementById('email');
    let email = await verificarEmail(valorEmail);

    const cep = document.getElementById('cep').value;
    !cep ? document.getElementById('erro-cepVazio').classList.remove('hidden') :
        document.getElementById('erro-cepVazio').classList.add('hidden');

    const estado = document.getElementById('uf').value;
    !estado ? document.getElementById('erro-ufVazio').classList.remove('hidden') :
        document.getElementById('erro-ufVazio').classList.add('hidden');

    const cidade = document.getElementById('localidade').value;
    !cidade ? document.getElementById('erro-localVazio').classList.remove('hidden') :
        document.getElementById('erro-localVazio').classList.add('hidden');

    const bairro = document.getElementById('bairro').value;
    !bairro ? document.getElementById('erro-bairroVazio').classList.remove('hidden') :
        document.getElementById('erro-bairroVazio').classList.add('hidden');

    const logradouro = document.getElementById('logradouro').value;
    !logradouro ? document.getElementById('erro-logrVazio').classList.remove('hidden') :
        document.getElementById('erro-logrVazio').classList.add('hidden');

    let valorComplementos = document.getElementById('complementos');
    let complementos = await verificarComplementos(valorComplementos);

    // Aqui você envia os dados usando fetch()
    try {
        if (!telefone || !email || !cep || !estado || !cidade || !bairro || !logradouro || !complementos) {
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
        texto.innerText = "Finalizar Cadastro";
    }, 2000);
});