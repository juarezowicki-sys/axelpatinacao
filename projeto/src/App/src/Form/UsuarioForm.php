<?php

declare(strict_types=1);

namespace App\Form;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\StringLength;
use Laminas\Filter\Digits;
use Laminas\Validator\Regex;
use Laminas\Validator;

class UsuarioForm extends Form implements InputFilterProviderInterface
{
    public function __construct($name = null)
    {
        parent::__construct('usuario');

        $this->add([
            'name'       => 'nome',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Nome / Razão social * ',
            ],
            'attributes' => [
                'id'       => 'nome',
                'onblur' => 'verificarNome(this)',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'Nome ou Razão Social completos',
            ],
        ]);
        $this->add([
            'name'       => 'documento',
            'type'       => Element\Tel::class,
            'options'    => [
                'label' => 'CPF ou CNPJ *',
            ],
            'attributes' => [
                'onblur' => 'verificarDocumento(this)',
                'oninput' => 'mascaraDocumento(this)',
                'maxlength' => '18',
                'id'       => 'documento',
                'class' => 'block w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => '000.000.000-00 ou 00.000.000/0000-00',
            ],
        ]);
        $this->add([
            'name'       => 'telefone',
            'type'       => Element\Tel::class,
            'options'    => [
                'label' => 'Telefone * ',
            ],
            'attributes' => [
                'id'       => 'telefone',
                'onblur' => 'verificarTelefone(this)',
                'oninput' => 'mascaraTelefone(this)',
                'maxlength' => '15',
                'class' => 'block w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => '(00) 90000-0000 ou (00) 0000-0000',
            ],
        ]);
        $this->add([
            'name'       => 'email',
            'type'       => Element\Email::class,
            'options'    => [
                'label' => 'E-mail * ',
            ],
            'attributes' => [
                'id'       => 'email',
                'onblur' => 'verificarEmail(this)',
                'class' => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'Seu e-mail principal',
            ],
        ]);
        $this->add([
            'name'       => 'cep',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'CEP *',
            ],
            'attributes' => [
                'id'       => 'cep',
                'oninput' => 'mascaraCEP(this)',
                'maxlength' => '9',
                'onblur' => 'verificarCep(this)',
                'class' => 'bg-gray-50 block shadow-sm w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => '00000-000 tipo autocomplete',
            ],
        ]);
        $this->add([
            'name'       => 'uf',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Sigla do seu Estado * ',
            ],
            'attributes' => [
                'id'   => 'uf',
                'maxlength' => '2',
                'class'  => 'bg-gray-50 w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'Sigla do seu Estado - ex.: RS',
            ],
        ]);
        $this->add([
            'name'       => 'localidade',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Cidade * ',
            ],
            'attributes' => [
                'id'       => 'localidade',
                'maxlength' => '100',
                'class'  => 'bg-gray-50 w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'ex.: Sapucaia do Sul',
            ],
        ]);

        $this->add([
            'name'       => 'bairro',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Bairro * ',
            ],
            'attributes' => [
                'id'       => 'bairro',
                'maxlength' => '100',
                'class'  => 'bg-gray-50 w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'Nome do seu bairro',
            ],
        ]);

        $this->add([
            'name'       => 'logradouro',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Logradouro *',
            ],
            'attributes' => [
                'id'       => 'logradouro',
                'maxlength' => '100',
                'class' => 'bg-gray-50 w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'Rua Av estrada etc.',
            ],
        ]);

        $this->add([
            'name'       => 'complementos',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Complementos do endereço *',
            ],
            'attributes' => [
                'id'       => 'complementos',
                'onblur' => 'verificarComplementos(this)',
                'maxlength' => '100',
                'class' => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'número do prédio, bloco, ap, sala, lote, etc.',
            ],
        ]);
        $this->add([
            'name' => 'password',
            'type' => \Laminas\Form\Element\Password::class,
            'options' => [
                'label' => 'Crie sua Senha',
            ],
            'attributes' => [
                'id'       => 'password',
                'onblur' => 'verificarPassword(this)',
                'class' => 'block w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'required' => 'required',
                'placeholder' => 'min. 8 carac. e mín. 1 letra e 1 número',
            ],
        ]);

        // Campo de Confirmação
        $this->add([
            'name' => 'password_confirm',
            'type' => \Laminas\Form\Element\Password::class,
            'options' => [
                'label' => 'Confirme a Senha',
            ],
            'attributes' => [
                'id'       => 'password_confirm',
                'onblur' => 'verificarConfirm(this)',
                'class' => 'block w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'required' => 'required',
                'placeholder' => 'repita a sua senha aqui',
            ],
        ]);
        // Adicionar botão de envio
        $this->add([
            'name'       => 'submit',
            'type'       => Element\Submit::class,
            'attributes' => [
                'id'       => 'submit',
                'value' => 'Finalizar Cadastro',
                'class' => 'btn btn-primary cursor-pointer w-full max-w-full mx-auto mt-4 px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-300',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'nome'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe o seu nome completo',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 7,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => 'Seu nome deve ter mais de sete letras',
                                StringLength::TOO_LONG  => 'Seu nome deve ter menos de 100 letras',
                            ],
                        ],
                    ],
                ],
            ],
            'documento' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => Digits::class], // Transforma "123.456.789-00" em "12345678900"
                ],
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'informe seu CPF ou CNPJ',
                            ],
                        ],
                    ],

                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 11,
                            'max' => 14,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O CPF  deve ter 11 números e o CNPJ 14 números',
                                StringLength::TOO_LONG  => 'O CPF  deve ter 11 números e o CNPJ 14 números',
                            ],
                        ],
                    ],
                ],
            ],
            'telefone' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => Digits::class], // Remove parênteses, espaços e traços
                ],
                'validators' => [
                    [
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => '/^[a-z0-9]+$/i', // Sua regex aqui
                        ],
                    ],
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'min' => 10,
                            'max' => 11,
                            'messages' => [
                                StringLength::TOO_SHORT => 'celular  deve ter 11 números e telefone 10 números',
                                StringLength::TOO_LONG  => 'celular  deve ter 11 números e telefone 10 números',
                            ],
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe o seu telefone',
                            ],
                        ],
                    ],
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 10, // Fixo com DDD
                            'max' => 11, // Celular com 9 dígitos + DDD
                            'messages' => [
                                StringLength::TOO_SHORT => '10',
                                StringLength::TOO_LONG  => '11',
                            ],
                        ],
                    ],
                ],
            ],
            'email' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'informe o seu e-mail',
                            ],
                        ],
                    ],
                    [
                        'name' => Validator\EmailAddress::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para falha na validação de e-mail
                                Validator\EmailAddress::INVALID_FORMAT => 'seu email tem um formato inválido',
                            ],
                        ],
                    ],
                ],
            ],
            'cep'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe o seu CEP',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 9,
                            'max' => 9,
                            'messages' => [
                                StringLength::TOO_SHORT => 'o cep deve ter 9 números',
                                StringLength::TOO_LONG  => 'o cep deve ter 9 números',
                            ],
                        ],
                    ],

                ],
            ],
            'uf'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe a sigla do seu estado',
                            ],
                        ],
                    ],
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 2,
                            'max' => 3,
                            'messages' => [
                                StringLength::TOO_SHORT => '2',
                                StringLength::TOO_LONG  => '3',
                            ],
                        ],
                    ],
                ],
            ],
            'localidade'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 3,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => '3',
                                StringLength::TOO_LONG  => '100',
                            ],
                        ],
                    ],
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nome da sua cidade',
                            ],
                        ],
                    ],
                ],
            ],
            'bairro'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => '1',
                                StringLength::TOO_LONG  => '100',
                            ],
                        ],
                    ],

                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nome do seu bairro',
                            ],
                        ],
                    ],
                ],
            ],

            'logradouro'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 3,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => '3',
                                StringLength::TOO_LONG  => '100',
                            ],
                        ],
                    ],
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nome do seu logradouro',
                            ],
                        ],
                    ],
                ],
            ],

            'complementos'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => '1',
                                StringLength::TOO_LONG  => '100',
                            ],
                        ],
                    ],
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe os complementos do seu endereço',
                            ],
                        ],
                    ],
                ],
            ],
            'password' => [
                'required' => true,
                'filters'  => [['name' => \Laminas\Filter\StringTrim::class]],
                'validators' => [
                    [
                        'name' => \Laminas\Validator\StringLength::class,
                        'options' => [
                            'min' => 8,
                            'message' => 'A senha deve conter pelo menos uma letra e um número e no mínimo 8 dígitos',
                        ],
                    ],
                    [
                        'name' => \Laminas\Validator\Regex::class,
                        'options' => [
                            // Regex: Pelo menos uma letra (minúscula ou maiúscula) e um número
                            'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
                            'messages' => [
                                \Laminas\Validator\Regex::NOT_MATCH => 'A senha deve conter pelo menos uma letra e um número e no mínimo 8 dígitos',
                            ],
                        ],
                    ],
                ],
            ],
            'password_confirm' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => \Laminas\Validator\Identical::class,
                        'options' => [
                            'token' => 'password', // Compara com o campo 'password' acima
                            'messages' => [
                                \Laminas\Validator\Identical::NOT_SAME => 'As senhas digitadas não são iguais.',
                            ],
                        ],
                    ],
                ],
            ],

        ];
    }

    public function getInputFilterConfig(): array
    {
        return [
            'documento' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => \Laminas\Validator\Db\NoRecordExists::class,
                        'options' => [
                            'table'   => 'usuarios',
                            'field'   => 'documento',
                            'adapter' => $this->getOption('db_adapter'), // Precisamos passar o adapter para o Form
                            'messages' => [
                                \Laminas\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Este CPF/CNPJ já está cadastrado no sistema.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
