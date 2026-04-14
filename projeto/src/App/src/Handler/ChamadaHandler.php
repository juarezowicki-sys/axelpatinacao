<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ChamadaHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly TemplateRendererInterface $template,
        private readonly RouterInterface $router,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $idTurma = (int) $request->getAttribute('id');
        $sql = new Sql($this->adapter);

        // 1. BUSCAR DADOS DA TURMA
        $selectTurma = $sql->select('turmas')->where(['id' => $idTurma]);
        $statement = $sql->prepareStatementForSqlObject($selectTurma);
        $turma = $statement->execute()->current();

        if (!$turma) {
            return new HtmlResponse("<h1>Turma não encontrada!</h1>", 404);
        }

        $opcoesMonitores = [];
        // 2. BUSCAR MONITORES
        $selectMonitoresUsuarios = $sql->select('usuarios')->where(['role' => 'monitor']);
        $selectMonitoresUsuarios->columns(['nome']);
        $statementMonitoresUsuarios = $sql->prepareStatementForSqlObject($selectMonitoresUsuarios);
        $monitoresUsuariosResult = $statementMonitoresUsuarios->execute();
        $monitoresUsuarios = iterator_to_array($monitoresUsuariosResult); // CONVERSÃO AQUI
        //$opcoesMonitores[] = $monitoresUsuarios;
        foreach ($monitoresUsuarios as $row) {
            $opcoesMonitores[] = $row['nome']; // Monitores que estão em 'usuarios'
        }
        $selectMonitoresAulas = $sql->select('aulas');
        $selectMonitoresAulas->columns(['monitor', 'monitor1', 'monitor2']);
        $statementMonitoresAulas = $sql->prepareStatementForSqlObject($selectMonitoresAulas);
        $monitoresAulasResult = $statementMonitoresAulas->execute();
        $monitoresAulas = iterator_to_array($monitoresAulasResult); // CONVERSÃO AQUI
        // $opcoesMonitores[] = $monitoresAulas;
        foreach ($monitoresAulas as $row) {
            $opcoesMonitores[] = $row['monitor']; // Monitores que estão em 'aulas'
        }
        $selectMonitoresTurmas = $sql->select('turmas');
        $selectMonitoresTurmas->columns(['monitor']);
        $statementMonitoresTurmas = $sql->prepareStatementForSqlObject($selectMonitoresTurmas);
        $monitoresTurmasResult = $statementMonitoresTurmas->execute();
        $monitoresTurmas = iterator_to_array($monitoresTurmasResult); // CONVERSÃO AQUI
        // $opcoesMonitores[] = $monitoresTurmas;
        foreach ($monitoresTurmas as $row) {
            $opcoesMonitores[] = $row['monitor']; // Monitores que estão em 'turmas'
        }
        // 1. Limpeza e Ordenação unificada dos Monitores
        $monitores = array_unique(array_filter($opcoesMonitores));
        sort($monitores);

        // 3. BUSCAR ATLETAS DA TURMA
        // Primeiro, pegamos o nome da turma que acabamos de buscar
        $nomeDaTurmaAtual = $turma['nome'];

        $selectAtletas = $sql->select(['a' => 'atletas']); // 'a' é o apelido para atletas
        $selectAtletas->columns(['nome', 'patins', 'titular']); // selecione as colunas que você já usa
        // Faz o JOIN com a tabela usuarios (apelidada de 'u')
        $selectAtletas->join(
            ['u' => 'usuarios'],          // Tabela alvo
            'a.titular = u.nome',         // Condição: titular do atleta = nome do usuário
            ['telefone'],                 // Colunas que queremos trazer da tabela usuarios
            $selectAtletas::JOIN_LEFT      // LEFT JOIN para não excluir atletas caso o titular não exista em usuarios
        );
        $selectAtletas->where->nest()
            ->equalTo('a.turma01', $nomeDaTurmaAtual)
            ->or
            ->equalTo('a.turma02', $nomeDaTurmaAtual)
            ->or
            ->equalTo('a.turma03', $nomeDaTurmaAtual)
            ->unnest();
        $statementAtletas = $sql->prepareStatementForSqlObject($selectAtletas);
        $atletas = iterator_to_array($statementAtletas->execute());

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            // 1. IDs ou Nomes dos que foram marcados no formulário
            $atletasPresentesArray = isset($data['atletas_presentes']) ? (array) $data['atletas_presentes'] : [];
            $presentes = implode(', ', $atletasPresentesArray);

            // 2. Extrair a lista de nomes de TODOS os atletas da turma que vieram do banco
            // Substitua 'nome' pela chave correta que você usa no 'value' do checkbox
            $todosOsAtletasNomes = array_column($atletas, 'nome');

            // 3. Comparar as duas listas para descobrir quem faltou
            $atletasFaltantesArray = array_diff($todosOsAtletasNomes, $atletasPresentesArray);
            $ausentes = implode(', ', $atletasFaltantesArray);



            // Formata horários e datas para o padrão INT do seu banco
            $inicio = (int) str_replace(':', '', $data['inicio']);
            $termino = (int) str_replace(':', '', $data['termino']);
            $dataAula = implode('', array_reverse(explode('/', $data['data'])));

            $tableAulas = new TableGateway('aulas', $this->adapter);
            try {
                $tableAulas->insert([
                    'nivel'           => $data['nivel'],
                    'local'           => $data['local'],
                    'dia'             => $data['dia'],
                    'inicio'          => $inicio,
                    'termino'         => $termino,
                    'data'            => $dataAula,
                    'monitor'        => $data['monitor'],
                    'monitor1'        => $data['monitor1'],
                    'monitor2'        => $data['monitor2'],
                    'nota'            => $data['nota'] ?? '',
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                ]);

                $flashMessages->flash('sucesso', 'a Aula foi registrada com sucesso!');

                return new \Laminas\Diactoros\Response\RedirectResponse($this->router->generateUri('aulas.listar'));
            } catch (\Exception $e) {
                return new HtmlResponse("<h1>Erro ao registrar a Aula: " . $e->getMessage() . "</h1>");
            }
        }

        // 5. TRATAR HORÁRIOS PARA EXIBIÇÃO (0800 -> 08:00)
        $formatarHora = fn($h) => substr_replace(str_pad((string)$h, 4, '0', STR_PAD_LEFT), ':', 2, 0);
        $turma['inicio_formatado'] = $formatarHora($turma['inicio']) . 'hs';
        $turma['termino_formatado'] = $formatarHora($turma['termino']) . 'hs';

        return new HtmlResponse($this->template->render('app::chamada', [
            'turma'     => $turma,
            'monitores' => $monitores,
            'atletas'   => $atletas,
            'dataHoje'  => date('d/m/Y')
        ]));
    }
}
