// Seleciona todos os inputs que possuem um datalist associado
document.querySelectorAll('input[list]').forEach(el => {

    // Quando o usuário clica ou foca no campo
    el.addEventListener('focus', function () {
        this.oldValue = this.value; // Salva o valor atual (ex: "iniciante01")
        this.value = ''; // Limpa para o navegador mostrar todas as opções
    });

    // Quando o usuário clica fora do campo
    el.addEventListener('blur', function () {
        // Se ele não escolheu nada novo, devolve o valor original
        if (this.value === '') {
            this.value = this.oldValue;
        }
    });

    // Opcional: Se ele escolher uma opção da lista, o 'blur' mantém a nova escolha
});