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

final class EditarAulaHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly TemplateRendererInterface $template,
        private readonly RouterInterface $router,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);
        // Extrai o nome do usuario (detalhes) e o role (primeira posição do array)
        $nomeLogado = $userData['details']['nome'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';

        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $idAula = (int) $request->getAttribute('id');
        $sql = new Sql($this->adapter);

        // 1. BUSCAR DADOS DA AULA
        $selectAula = $sql->select('aulas')->where(['id' => $idAula]);
        $statement = $sql->prepareStatementForSqlObject($selectAula);
        $aula = $statement->execute()->current();

        if (!$aula) {
            $flashMessages->flash('erro', 'Aula não encontrada!');
            return new \Laminas\Diactoros\Response\RedirectResponse($this->router->generateUri('aulas.listar'));
        }

        // 2. BUSCAR MONITORES
        $opcoesMonitores = [];
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

        if ($request->getMethod() === 'POST') {

            $data = $request->getParsedBody();

            // 1. IDs ou Nomes dos que foram marcados no formulário
            $atletasPresentesArray = isset($data['atletas_presentes']) ? (array) $data['atletas_presentes'] : [];
            $presentes = implode(', ', $atletasPresentesArray);
            // 2. Extrair a lista de nomes de TODOS os atletas da turma que vieram do banco
            $partes = array_filter([$aula['presentes'], $aula['ausentes']]);
            $stringLonga = implode(', ', $partes);
            // O SEGREDO: transforma a string de volta em um array real de nomes
            $todosOsAtletasNomes = explode(', ', $stringLonga);
            // 3. Agora o array_diff vai funcionar perfeitamente
            $atletasFaltantesArray = array_diff($todosOsAtletasNomes, $atletasPresentesArray);
            $ausentes = implode(', ', $atletasFaltantesArray);


            // Formata horários e datas para o padrão INT do seu banco
            $inicio = (int) str_replace(':', '', $data['inicio']);
            $termino = (int) str_replace(':', '', $data['termino']);
            $dataAula = implode('', array_reverse(explode('/', $data['data'])));

            $tableAulas = new TableGateway('aulas', $this->adapter);

            try {
                $tableAulas->update([
                    'nivel'           => $data['nivel'],
                    'local'           => $data['local'],
                    'dia'             => $data['dia'],
                    'inicio'          => $inicio,
                    'termino'         => $termino,
                    'data'            => $dataAula,
                    'monitor1'        => $data['monitor1'],
                    'monitor2'        => $data['monitor2'],
                    'nota'            => $data['nota'] ?? '',
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                ], ['id' => $idAula]);

                $flashMessages->flash('sucesso', 'a Aula foi editada com sucesso!');
                return new \Laminas\Diactoros\Response\RedirectResponse($this->router->generateUri('aulas.listar'));
            } catch (\Exception $e) {
                if ($role === 'admin') {
                    $flashMessages->flash('erro', 'Erro ao editar a aula: " . $e->getMessage() . "!');
                } else {
                    $flashMessages->flash('erro', 'Não foi possível editar a aula. Comunique o admin do site.');
                }
                return new \Laminas\Diactoros\Response\RedirectResponse($this->router->generateUri('aulas.listar'));
            }
        }

        // TRATAR HORÁRIOS PARA EXIBIÇÃO (0800 -> 08:00)
        $formatarHora = fn($h) => substr_replace(str_pad((string)$h, 4, '0', STR_PAD_LEFT), ':', 2, 0) . 'hs';
        $aula['inicio_formatado'] = $formatarHora($aula['inicio']);
        $aula['termino_formatado'] = $formatarHora($aula['termino']);
        //  TRATAR data PARA EXIBIÇÃO (20261210 -> 10/12/2026)
        $formatarData = fn($h) => date('d/m/Y', strtotime((string)$aula['data']));
        $aula['data']  =  $formatarData($aula['data']);

        return new HtmlResponse($this->template->render('app::editar-aula', [
            'aula'     => $aula,
            'monitores' => $monitores,
            // 'atletas'   => $atletas,
            'dataHoje'  => date('d/m/Y')

        ]));
    }
}
