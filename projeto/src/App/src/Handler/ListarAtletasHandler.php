<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListarAtletasHandler implements RequestHandlerInterface
{
    private $adapter;
    private $template;

    public function __construct(AdapterInterface $adapter, TemplateRendererInterface $template)
    {
        $this->adapter = $adapter;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
        $userData = $session->get(\Mezzio\Authentication\UserInterface::class);

        // Extrai dados da sessão
        $nomeLogado = $userData['details']['nome'] ?? null;
        $roles = $userData['roles'] ?? [];
        $role = $roles[0] ?? 'guest';

        $atletas = []; // Inicializa como array vazio para evitar erro de variável indefinida

        // Pega o atributo; se não existir, define como null
        $titularAttr = $request->getAttribute('titular');

        // Só faz o urldecode e trim se o atributo realmente existir na URL
        $titularGET = $titularAttr ? trim(urldecode($titularAttr)) : null;

        // SÓ executa a busca se tivermos um nome logado, evitando erro no SQL
        if ($nomeLogado) {
            $sql = new \Laminas\Db\Sql\Sql($this->adapter);
            $selectAtletas = $sql->select(['a' => 'atletas']);
            $selectAtletas->columns(['id', 'nome', 'patins', 'turma01', 'turma02', 'turma03', 'titular', 'nascimento', 'cadastro']);

            if ($role === 'monitor') {
                // 1. Criamos a subquery apenas com a coluna necessária
                $subSelectTurmas = $sql->select('turmas')
                    ->columns(['nome'])
                    ->where(['monitor' => $nomeLogado]);

                // 2. Aplicamos o filtro nas 3 colunas de turma
                // Usamos o objeto de subquery diretamente dentro do in()
                $selectAtletas->where->nest()
                    ->in('a.turma01', $subSelectTurmas)
                    ->or
                    ->in('a.turma02', $subSelectTurmas)
                    ->or
                    ->in('a.turma03', $subSelectTurmas)
                    ->unnest();
            } else if ($role === 'titular') {
                // Regra para o titular logado ver seus próprios atletas
                $selectAtletas->where(['a.titular' => $nomeLogado]);
            } else if ($role === 'admin') {
                // Se for admin e houver um titular na URL, filtra por ele. Caso contrário, lista tudo.
                if ($titularGET) {
                    $selectAtletas->where(['a.titular' => $titularGET]);
                }
            } else {
                $selectAtletas->where->expression('1 = 0', []);
            }

            $selectAtletas->order('a.nome ASC');

            try {
                $statementAtletas = $sql->prepareStatementForSqlObject($selectAtletas);
                $result = $statementAtletas->execute();
                $atletas = iterator_to_array($result);
            } catch (\Exception $e) {
                $atletas = [];
            }
        }

        // Recupera mensagens flash
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $mensagensSucesso = $flashMessages->getFlash('sucesso');
        $mensagensErro = $flashMessages->getFlash('erro');

        // Lógica de Renderização
        if ($role === 'titular' && $nomeLogado) {
            return new HtmlResponse($this->template->render('app::home-atleta', [
                'atletas' => $atletas,
                'mensagensSucesso' => $mensagensSucesso
            ]));
        }

        return new HtmlResponse($this->template->render('app::listar-atletas', [
            'role' => $role,
            'atletas' => $atletas,
            'mensagensErro' => $mensagensErro,
            'mensagensSucesso' => $mensagensSucesso,
        ]));
    }
}
