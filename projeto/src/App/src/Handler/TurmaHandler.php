<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Form\Element\Select;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use App\Form\TurmaForm;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Db\TableGateway\TableGateway;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Authentication\UserInterface;

final class TurmaHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly \Laminas\Db\Adapter\AdapterInterface $adapter,
        private readonly \Mezzio\Router\RouterInterface $router,
        private readonly ?\Mezzio\Template\TemplateRendererInterface $template = null,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $flashMessages = $request->getAttribute(\Mezzio\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE);

        $form = new \App\Form\TurmaForm($this->adapter); // Passando o adapter aqui
    
        $sql = new \Laminas\Db\Sql\Sql($this->adapter);
        $select = $sql->select('turmas');
        $select->columns(['nome', 'nivel', 'local', 'monitor', 'dia', 'inicio', 'termino']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        $sql = new \Laminas\Db\Sql\Sql($this->adapter);
        $select = $sql->select('usuarios');
        $select->columns(['nome']);
        $select->where->like('role', 'monitor');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultados = $statement->execute();

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
        foreach ($resultados as $linha) {
            $opcoesMonitores[] = $linha['nome'];
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

        $message = '';

        if ($request->getMethod() === 'POST') {

            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['submit']);

                // Formata horários para formato '0000'
                $data['inicio'] = (int) str_replace(':', '', $data['inicio']);
                $data['termino'] = (int) str_replace(':', '', $data['termino']);

                // $data['nome'] = '';
                $tableGateway = new \Laminas\Db\TableGateway\TableGateway('turmas', $this->adapter);
                try {
                    // 2. Opcional: Tratar campos vazios da turma como NULL
                    foreach ($data as $key => $value) {
                        if ($value === '') {
                            $data[$key] = null;
                        }
                    }

                    // 3. Inserir no banco
                    $tableGateway->insert($data);
                    $flashMessages->flash('sucesso', 'A nova turma foi criada com sucesso!');

                    return new RedirectResponse($this->router->generateUri('turmas.listar'));
                } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $e) {
                    // Se der erro de "Duplicate entry" (Código 1062)
                    if (strpos($e->getMessage(), '1062') !== false) {
                        $message = "Ops! O nome '{$data['nome']}' já existe. Por favor, escolha outro.";
                    } else {
                        // Se for outro erro, exibe uma mensagem genérica
                        $message = "Erro ao salvar os dados. Tente novamente.";
                    }
                }
            } else {
                $message = 'Corrija os erros em vermelho';
            }
        }

        return new HtmlResponse($this->template->render('app::turma', [
            'form'    => $form,
            'request' => $request,
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
