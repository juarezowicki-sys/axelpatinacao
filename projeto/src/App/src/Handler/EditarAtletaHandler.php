<?php

declare(strict_types=1);

namespace App\Handler;

use App\Form\EditarAtletaForm;
use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Form\Element\Select;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditarAtletaHandler implements RequestHandlerInterface
{
    private $adapter;
    private $template;
    private $router;

    public function __construct(
        AdapterInterface $adapter,
        TemplateRendererInterface $template,
        RouterInterface $router
    ) {
        $this->adapter  = $adapter;
        $this->template = $template;
        $this->router   = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        /** @var \Laminas\Db\TableGateway $tableGateway */
        
        $form = new EditarAtletaForm();

        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);
        // Extrai o nome do usuario (detalhes) e o role (primeira posição do array)
        $nomeLogado = $userData['details']['nome'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';

       // Busca o atleta no banco (retorna um ArrayObject ou false)
       $tableGateway = new TableGateway('atletas', $this->adapter);
        /** @var \Laminas\Db\TableGateway $tableGateway */
        $atleta = $tableGateway->select(function ($select) use ($role, $id, $nomeLogado) {
            if ($role === 'admin' || $role === 'monitor') {
                //  Vê todos os atletas
                $select->where(['id' => $id]);
            } elseif ($role === 'titular') {
                // Titular: Vê apenas atletas onde a coluna 'nome' é o seu nome
                $select->where(['titular' => $nomeLogado, 'id' => $id]);
            } else {
                // Guest ou sem role: Retorna lista vazia
                $select->where->equalTo(1, 0);
            }
        })->current();

        if (!isset($atleta)) {
            // 1. Obter o contêiner de flash da requisição
            $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
            // 2. Criar uma flash message (chave, valor)
            $flashMessages->flash('erro', 'Este Titular não tem um Atleta com o ID  ' . $id ,1);
            return new RedirectResponse($this->router->generateUri('atletas.listar'));
        }

        // Busca o atleta no banco (retorna um ArrayObject ou false)
        // $atleta = $tableGateway->select(['id' => $id])->current();

        $atleta->nascimento = strlen($atleta->nascimento) === 8
            ? substr($atleta->nascimento, 0, 2) . '/' . substr($atleta->nascimento, 2, 2) . '/' . substr($atleta->nascimento, 4)
            : $atleta->nascimento;

        $turmasGateway = new TableGateway('turmas', $this->adapter);
        $turmas = $turmasGateway->select(function ($select) {
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
            // $t->nome é o <option value>, $t->nivel ... etc. é o texto
        }
        // 4. Passar as opções para o elemento select do formulário
        // Get the element and cast it to the correct type
        $element01 = $form->get('turma01');
        $element02 = $form->get('turma02');
        $element03 = $form->get('turma03');
        if ($element01 instanceof Select && $element02 instanceof Select && $element03 instanceof Select) {
            $element01->setValueOptions($options);
            $element02->setValueOptions($options);
            $element03->setValueOptions($options);
        }
       
        if ($role !== 'admin') {
            $element04 = $form->get('nome');
            $element05 = $form->get('titular');
            $element06 = $form->get('nascimento');
            $element04->setAttribute('readonly', true);
            $element05->setAttribute('readonly', true);
            $element06->setAttribute('readonly', true);
        }

        if (! $atleta) {
            return new RedirectResponse($this->router->generateUri('atletas.listar'));
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if ($form->isValid()) {

                $validData = $form->getData();

                unset($validData['submit']); // Remove botão se existir no array

                if ($tableGateway->update($validData, ['id' => $id])) {
                    // 1. Obter o contêiner de flash da requisição
                    $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                    // 2. Criar uma flash message (chave, valor)
                    $flashMessages->flash('sucesso', 'O perfil do(a) atleta - ' . $validData['nome'] . ' - foi editado com sucesso!');

                    return new RedirectResponse($this->router->generateUri('atletas.listar'));
                }
            }
        } else {
            // Se for GET, preenche o form com os dados vindos do banco
            // Convertemos para array para garantir a compatibilidade com o formulário
            $form->setData((array) $atleta);
        }
        if ($role === 'admin') {
            return new HtmlResponse($this->template->render('app::editar-atleta', [
                'form' => $form,
                'id'   => $id
            ]));
        } else {
            return new HtmlResponse($this->template->render('app::editar-atleta', [
                'form' => $form,
                'id'   => $id
            ]));
        }
    }
}
