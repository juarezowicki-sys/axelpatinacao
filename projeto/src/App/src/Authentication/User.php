<?php

declare(strict_types=1);

namespace App\Authentication;

use Mezzio\Authentication\UserInterface;

class User implements UserInterface
{
public function __construct(
    private string $identity,
    private iterable $roles = [],
    private array $details = [],
) {}

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function getRoles(): iterable
    {
        return $this->roles;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Este método é o que o seu Middleware usa para pegar o 'nome'
     */
    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }
}
