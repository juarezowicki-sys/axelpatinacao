<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HomeAulaHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly TemplateRendererInterface $template,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $idAula = (int) $request->getAttribute('id');
        $sql = new Sql($this->adapter);

        // 1. BUSCAR DADOS DA AULA
        $selectAula = $sql->select('aulas')->where(['id' => $idAula]);
        $statement = $sql->prepareStatementForSqlObject($selectAula);
        $aula = $statement->execute()->current();

        if (!$aula) {
            return new HtmlResponse("<h1>Aula não encontrada!</h1>", 404);
        }

        // TRATAR HORÁRIOS PARA EXIBIÇÃO (0800 -> 08:00)
        $formatarHora = fn($h) => substr_replace(str_pad((string)$h, 4, '0', STR_PAD_LEFT), ':', 2, 0) . 'hs';
        $aula['inicio_formatado'] = $formatarHora($aula['inicio']);
        $aula['termino_formatado'] = $formatarHora($aula['termino']);
        //  TRATAR data PARA EXIBIÇÃO (20261210 -> 10/12/2026)
        $formatarData = fn($h) => date('d/m/Y', strtotime((string)$aula['data']));
        $aula['data']  =  $formatarData($aula['data']);

        return new HtmlResponse($this->template->render('app::home-aula', [
            'aula'     => $aula
        ]));
    }
}
