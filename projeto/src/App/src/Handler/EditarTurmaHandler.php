<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Form\TurmaForm;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EditarTurmaHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly \Laminas\Db\Adapter\AdapterInterface $adapter,
        private readonly \Mezzio\Router\RouterInterface $router,
        private readonly ?\Mezzio\Template\TemplateRendererInterface $template = null,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {



        $id = (int) $request->getAttribute('id');
        $this->$id = $id;

        $flashMessages = $request->getAttribute(\Mezzio\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE);

        $message = '';

        $form = new TurmaForm($this->adapter, $id);

        $tableGateway = new \Laminas\Db\TableGateway\TableGateway('turmas', $this->adapter);
        /** @var \Laminas\Db\TableGateway $tableGateway */
        $turmaAtual = (array) $tableGateway->select(['id' => $id])->current();
        
        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);
        // Extrai o nome do titular (detalhes) e o role (primeira posição do array)
        $nomeLogado = $userData['details']['nome'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';

       
  //var_dump($turmaAtual['monitor']);exit;
  $monitor = isset($turmaAtual['monitor']) ? $turmaAtual['monitor'] : NULL;
        if ($role === 'monitor' && $nomeLogado !== $monitor) {
           unset($turmaAtual);
        }

        if (!isset($turmaAtual)) {
             $flashMessages->flash('erro', 'A turma que você está buscando  não existe!');
            return new RedirectResponse($this->router->generateUri('turmas.listar'));
        }

        // TRATAR HORÁRIOS PARA EXIBIÇÃO (0800 -> 08:00hs)
        $formatarHora = fn($h) => substr_replace(str_pad((string)$h, 4, '0', STR_PAD_LEFT), ':', 2, 0) . 'hs';
        $turmaAtual['inicio'] = $formatarHora($turmaAtual['inicio']);
        $turmaAtual['termino'] = $formatarHora($turmaAtual['termino']);



        // Se for GET, preenchemos o form com o que está no banco para este ID
        if ($request->getMethod() === 'GET') {
            $form->setData($turmaAtual);

            $element01 = $form->get('nome');
            $element01->setAttribute('readonly', true);
            $element01->setAttribute('placeholder', 'o nome não pode ser alterado');
        }

        $sql = new \Laminas\Db\Sql\Sql($this->adapter);
        $select = $sql->select('usuarios');
        $select->columns(['nome']);
        $select->where->like('role', 'monitor');
        $statement = $sql->prepareStatementForSqlObject($select);
        $results02 = $statement->execute();

        $sql = new \Laminas\Db\Sql\Sql($this->adapter);
        $select = $sql->select('turmas');
        $select->columns(['nome', 'nivel', 'local', 'monitor', 'dia', 'inicio', 'termino']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        $opcoesNomes = [];
        $opcoesNiveis = [];
        $opcoesLocais = [];
        $opcoesMonitores = [];
        $opcoesDias = [];
        $opcoesInicios = [];
        $opcoesTerminos = [];

        // 1. Processa os resultados das Turmas
        // 5. TRATAR HORÁRIOS PARA EXIBIÇÃO (0800 -> 08:00)
        $formatarHora = fn($h) => substr_replace(str_pad((string)$h, 4, '0', STR_PAD_LEFT), ':', 2, 0);
        foreach ($results as $row) {
            $opcoesNomes[]     = $row['nome'];
            $opcoesNiveis[]    = $row['nivel'];
            $opcoesLocais[]    = $row['local'];
            $opcoesMonitores[] = $row['monitor']; // Monitores que já estão em turmas
            $opcoesDias[]      = $row['dia'];
            $opcoesInicios[]    = $formatarHora($row['inicio']) . 'hs';
            $opcoesTerminos[] = $formatarHora($row['termino']) . 'hs';
        }

        // 2. Adiciona os Monitores da tabela de Usuários ao mesmo array
        foreach ($results02 as $row02) {
            $opcoesMonitores[] = $row02['nome'];
        }

        // 1. Limpeza e Ordenação unificada dos Monitores
        $opcoesMonitores = array_unique(array_filter($opcoesMonitores));
        sort($opcoesMonitores);

        // 2. Limpeza e Ordenação dos demais arrays (essencial aplicar a atribuição do filter)
        $opcoesNomes    = array_unique(array_filter($opcoesNomes));
        sort($opcoesNomes);
        $opcoesNiveis   = array_unique(array_filter($opcoesNiveis));
        sort($opcoesNiveis);
        $opcoesLocais   = array_unique(array_filter($opcoesLocais));
        sort($opcoesLocais);
        $opcoesDias     = array_unique(array_filter($opcoesDias));
        sort($opcoesDias);
        $opcoesInicios  = array_unique(array_filter($opcoesInicios));
        sort($opcoesInicios);
        $opcoesTerminos = array_unique(array_filter($opcoesTerminos));
        sort($opcoesTerminos);

        if ($request->getMethod() === 'POST') {

            $form->setData($request->getParsedBody());

            if ($form->isValid()) {

                $data = $form->getData();

                unset($data['submit']);
                unset($data['nome']);

                // Formata horários para formato '0000'
                $data['inicio'] = (int) str_replace(':', '', $data['inicio']);
                $data['termino'] = (int) str_replace(':', '', $data['termino']);

                try {
                    // Limpeza e Update seguro com WHERE ID
                    $data = array_map(fn($v) => $v === '' ? null : $v, $data);
                    $tableGateway->update($data, ['id' => $id]);
      
                    $flashMessages->flash('sucesso', 'Os dados foram alterados com sucesso!', 1);

                    return new RedirectResponse($this->router->generateUri('turmas.listar'));
                } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $e) {
                    // Se der erro de "Duplicate entry" (Código 1062)
                    if (strpos($e->getMessage(), '1062') !== false) {
                        $message = "editar - Ops! O nome '{$data['nome']}' já existe. Por favor, escolha outro.";
                    } else {
                        // Se for outro erro, exibe uma mensagem genérica
                        $message = "Erro ao salvar os dados. Tente novamente.";
                    }
                }
            } else {
                $message = 'Corrija os erros em vermelho';
            }
        }

        // 4. RENDERIZAÇÃO
        return new HtmlResponse($this->template->render('app::editar-turma', [
            'form'    => $form,
            'message' => $message,
            'nomes' => $opcoesNomes,
            'niveis' => $opcoesNiveis,
            'locais' => $opcoesLocais,
            'monitores' => $opcoesMonitores,
            'dias' => $opcoesDias,
            'inicios' => $opcoesInicios,
            'terminos' => $opcoesTerminos,
        ]));
    }
}
