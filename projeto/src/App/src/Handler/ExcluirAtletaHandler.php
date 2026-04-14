<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExcluirAtletaHandler implements RequestHandlerInterface
{
    private $adapter;
    private $router;

    // O construtor recebe o que a Factory envia
    public function __construct(AdapterInterface $adapter, RouterInterface $router)
    {
        $this->adapter = $adapter;
        $this->router  = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Pega o ID da URL
        $id = (int) $request->getAttribute('id');

        if ($id > 0) {
            
            $tableGateway = new TableGateway('atletas', $this->adapter);
            $tableGateway->delete(['id' => $id]);
        }

        // Redireciona de volta para a lista
        return new RedirectResponse($this->router->generateUri('atletas.listar'));
    }
}