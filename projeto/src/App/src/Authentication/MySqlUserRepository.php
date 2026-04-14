<?php

declare(strict_types=1);

namespace App\Authentication;

use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Authentication\UserInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;

class MySqlUserRepository implements UserRepositoryInterface
{
    public function __construct(private AdapterInterface $db) {}

    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('usuarios'); // Ajuste aqui se o nome da tabela for outro
        $select->where(['email' => $credential]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute()->current();
        // 1. Verifica se o usuário existe
        // 2. Compara a senha usando o campo 'password' do seu BD
if (! $result || 
    ! password_verify($password, (string) $result['password']) || 
    (int) $result['status'] !== 1 // <--- ADICIONE ESTA LINHA AQUI
) {
    return null;
}
        // Retorna o objeto User com o campo 'role' (nível)
        return new \App\Authentication\User(
            (string) $result['email'],
            [(string) $result['role']],
            [
                'id'   => $result['id'],
                'nome' => $result['nome'], // Verifique se a coluna no banco é 'nome'
                'role' => $result['role']
            ]
        );
    }

    public function findByEmail(string $email): ?array
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('usuarios');
        $select->where(['email' => $email]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute()->current();

        return $result ?: null;
    }

    public function findByCpf(string $cpfCnpj): ?array
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('usuarios');
        $select->where(['documento' => $cpfCnpj]); // Nome da sua coluna no BD

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute()->current();

        return $result ?: null;
    }
    public function findByToken(string $token): ?array
    {
        $sql = new Sql($this->db);
        $select = $sql->select('usuarios');
        $select->where(['token_confirmacao' => $token]);

        $result = $sql->prepareStatementForSqlObject($select)->execute()->current();
        return $result ?: null;
    }
}
