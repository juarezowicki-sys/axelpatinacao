<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Form\Element\Select;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use App\Form\AtletaForm;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Db\TableGateway\TableGateway;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Authentication\UserInterface;

final class AtletaHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly \Laminas\Db\Adapter\AdapterInterface $adapter,
        private readonly \Mezzio\Router\RouterInterface $router,
        private readonly ?\Mezzio\Template\TemplateRendererInterface $template = null,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);

        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';
        if ($role === 'monitor') {
            return new RedirectResponse('/login');
        }


        $form = new AtletaForm('atleta', ['db_adapter' => $this->adapter]);

        // Busca as categorias no banco (retorna um ArrayObject ou false)
        //   $tableGatewayTurmas = new TableGateway('turmas', $this->adapter);
        //  $turmas = $tableGatewayTurmas->select();

        $tableGateway = new TableGateway('turmas', $this->adapter);
        $turmas = $tableGateway->select(function ($select) {
            $select->order('local ASC');
            $select->order('dia DESC');
            $select->order('inicio ASC');
        });

        // 3. Formatar os dados para o Select (chave => valor)
        $options = [];
        $formatarHora = fn($h) => substr_replace(str_pad((string)$h, 4, '0', STR_PAD_LEFT), ':', 2, 0);
        foreach ($turmas as $t) {
            $inicio_formatado = $formatarHora($t->inicio) . 'hs';
            $termino_formatado = $formatarHora($t->termino) . 'hs';

            $options[$t->nome] = ' ' . $t->local . ' ' . $t->dia . ' ' . $inicio_formatado . ' às ' . $termino_formatado;
            // $t->nome é o <option value>, $t->local ... etc. é o texto
        }
        // 4. Passar as opções para o elemento select do formulário
        $element01 = $form->get('turma01');
        $element02 = $form->get('turma02');
        $element03 = $form->get('turma03');
        if ($element01 instanceof Select && $element02 instanceof Select && $element03 instanceof Select) {
            $element01->setValueOptions($options);
            $element02->setValueOptions($options);
            $element03->setValueOptions($options);
        }
        $element04 = $form->get('titular');

        // 1. Pega o objeto do usuário logado (injetado automaticamente pelo Mezzio)
        $user = $request->getAttribute(UserInterface::class);
        // 2. Pega o nome ou define o padrão
        $nome = $user ? $user->getDetail('nome') : '';

        $role = $user ? $user->getRoles('role') : '';

        if ($role[0] === 'titular') {
            $element04->setValue($nome);
            $element04->setAttribute('readonly', true);
        } elseif ($role[0] === 'admin' || $role[0] === 'monitor') {
            $sql = new \Laminas\Db\Sql\Sql($this->adapter);
            $select = $sql->select('usuarios');
            $select->columns(['nome']);
            $select->where->like('role', 'titular');
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultados = $statement->execute();

            $opcoesTitulares = [];
            foreach ($resultados as $row) {
                $opcoesTitulares[]     = $row['nome'];
            }

            $opcoesTitulares    = array_unique(array_filter($opcoesTitulares));
            sort($opcoesTitulares);
        }

        $message = '';

        if ($request->getMethod() === 'POST') {

            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();

                unset($data['submit']);
                $tableGateway = new \Laminas\Db\TableGateway\TableGateway('atletas', $this->adapter);
                try {
                    // 2. Opcional: Tratar campos vazios do segundo atleta como NULL
                    foreach ($data as $key => $value) {
                        if ($value === '') {
                            $data[$key] = null;
                        }
                    }

                    // inserir no banco
                    if ($tableGateway->insert($data)) {
                        // 1. Obter o contêiner de flash da requisição
                        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                        // 2. Criar uma flash message (chave, valor)
                        $flashMessages->flash('sucesso', 'O atleta foi cadastrado com sucesso!');
                    }
                    // 3. Inserir no banco
                    return new RedirectResponse($this->router->generateUri('atletas.listar'));
                } catch (\Exception $e) {
                    // Caso ocorra algum erro (ex: e-mail duplicado ou coluna faltando)
                    return new \Laminas\Diactoros\Response\HtmlResponse("<h1>Erro ao salvar: " . $e->getMessage() . "</h1>");
                }
            } else {
                $message = 'Corrija os erros em vermelho';
            }
        }

        if ($role[0] === 'admin' || $role[0] === 'monitor') {

            return new HtmlResponse($this->template->render('app::atleta', [
                'form'    => $form,
                'request' => $request,
                'message' => $message,
                'titulares' => $opcoesTitulares
            ]));
        } else {
            return new HtmlResponse($this->template->render('app::atleta', [
                'form'    => $form,
                'request' => $request,
                'message' => $message
            ]));
        }
    }
}
