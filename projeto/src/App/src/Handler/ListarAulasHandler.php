<?php

declare(strict_types=1);

namespace App\Handler; // Ajuste para o seu namespace real

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Select;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use DateTime;
use IntlDateFormatter;

class ListarAulasHandler implements RequestHandlerInterface
{
    private $adapter;
    private $renderer;

    public function __construct(AdapterInterface $adapter, TemplateRendererInterface $renderer)
    {
        $this->adapter = $adapter;
        $this->renderer = $renderer;
    }

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $flashMessages = $request->getAttribute(\Mezzio\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE);
    $session = $request->getAttribute(\Mezzio\Session\SessionMiddleware::SESSION_ATTRIBUTE);
    $userData = $session->get(\Mezzio\Authentication\UserInterface::class);

    $roles = $userData['roles'] ?? [];
    $role = $roles[0] ?? 'guest';
    $nomeLogado = $userData['details']['nome'] ?? null;

    $atletaUrl = $request->getAttribute('atleta');
    $tipoConsulta = $request->getAttribute('chama');

    // 1. INICIALIZAÇÃO DE VARIÁVEIS
    $aulas = [];
    $opcoesMeses = []; // Será preenchido dinamicamente abaixo
    $queryParams = $request->getQueryParams();
    $dataBusca = $queryParams['data_especifica'] ?? null;
    $mesFiltro = $queryParams['mes'] ?? date('Ym');
    $mesExibicao = "";

    // 2. VALIDAÇÃO DE TITULAR
    if ($role === 'titular') {
        $atletaTable = new \Laminas\Db\TableGateway\TableGateway('atletas', $this->adapter);
        $atletaValido = $atletaTable->select([
            'nome'    => $atletaUrl,
            'titular' => $nomeLogado
        ])->current();

        if (!$atletaValido) {
            return new HtmlResponse($this->renderer->render('app::listar-aulas', [
                'mensagensErro' => 'Este atleta não existe ou não está vinculado ao seu user.'
            ]));
        }
    }

    // 3. CONSTRUÇÃO DA QUERY
    if (in_array($role, ['admin', 'monitor', 'titular'])) {
        $select = new Select('aulas');

        if (!empty($dataBusca)) {
            $dataInt = (int) str_replace('-', '', $dataBusca);
            $select->where->equalTo('data', $dataInt);
            $mesExibicao = "Dia " . DateTime::createFromFormat('Y-m-d', $dataBusca)->format('d/m/Y');
        } else {
            $dataObj = DateTime::createFromFormat('Ym', $mesFiltro);
            $dataInicio = (int) $dataObj->format('Ym01');
            $dataFim    = (int) $dataObj->format('Ymt');
            
            $select->where->between('data', $dataInicio, $dataFim);
            
            $formatterLong = new IntlDateFormatter('pt_BR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, "MMMM 'de' yyyy");
            $mesExibicao = ucfirst($formatterLong->format($dataObj));
        }

        if ($role === 'monitor' && $nomeLogado) {
            $select->where->equalTo('monitor', $nomeLogado);
        } elseif ($role === 'titular') {
            $select->where->like($tipoConsulta, "%$atletaUrl%");
        }

        $select->order(['data DESC', 'inicio ASC']);
        $sql = new Sql($this->adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        foreach ($result as $row) {
            $aulas[] = $row;
        }


        $select = new Select('aulas');
        if ($role === 'monitor' && $nomeLogado) {
            $select->where->equalTo('monitor', $nomeLogado);
        } elseif ($role === 'titular') {
            $select->where->like($tipoConsulta, "%$atletaUrl%");
        }
        $select->order(['data DESC', 'inicio ASC']);
        $sql = new Sql($this->adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        // 4. EXECUÇÃO E GERAÇÃO DINÂMICA DAS OPÇÕES DO DROPDOWN
        $formatterMes = new IntlDateFormatter('pt_BR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, "MMMM / yyyy");
        foreach ($result as $row) {
            // Extrai o mês da data da aula (formato YYYYMMDD) para popular o select
            $dataAula = DateTime::createFromFormat('Ymd', (string)$row['data']);
            if ($dataAula) {
                $val = $dataAula->format('Ym');
                $label = ucfirst($formatterMes->format($dataAula));
                $opcoesMeses[$val] = $label; // O uso da chave $val evita duplicatas
            }

        }
        // Ordena os meses do mais recente para o mais antigo
        krsort($opcoesMeses);
    }

    // 5. RENDERIZAÇÃO FINAL
    return new HtmlResponse($this->renderer->render('app::listar-aulas', [
        'aulas'            => $aulas,
        'atleta'           => $atletaUrl,
        'tipoConsulta'     => $tipoConsulta,
        'role'             => $role,
        'mesExibicao'      => $mesExibicao,
        'mesAtual'         => $mesFiltro,
        'dataBusca'        => $dataBusca,
        'opcoesMeses'      => $opcoesMeses,
        'mensagensSucesso' => $flashMessages->getFlash('sucesso'),
        'mensagensErro'    => $flashMessages->getFlash('erro')
    ]));

        $mensagensErro = $flashMessages->flashNow('erro', 'Somente o administrador ou um monitor podem utilizar esta página', 1);
        $mensagensErro = $flashMessages->getFlash('erro');

        /** @var App\Handler $aulas */
        return new HtmlResponse($this->renderer->render('app::listar-aulas', [
            'mensagensErro' => $mensagensErro
        ]));
    }
}
