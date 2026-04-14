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

class AtletaForm extends Form implements InputFilterProviderInterface
{
    public function __construct($name = null)
    {
        parent::__construct('atleta');

        $this->add([
            'name'       => 'nome',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Nome do atleta  * ',
            ],
            'attributes' => [
                'id'       => 'nome',
                'onblur' => 'verificarNome(this)',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'nome completo',
            ],
        ]);
        $this->add([
            'name'       => 'titular',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Nome do responsável * ',
            ],
            'attributes' => [
                'list'  =>  'lista-titulares',
                'id'       => 'titular',
                'onblur' => 'verificarResponsavel(this)',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-200 bg-gray-100 outline-none border-blue-500',
                'placeholder' =>  'escolha um nome da lista',
            ]
        ]);
        $this->add([
            'name'       => 'nascimento',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Data de nascimento do atleta * ',
            ],
            'attributes' => [
                'maxlength' => '10',
                'id'       => 'nascimento',
                'oninput' => 'mascaraData(this)',
                'onblur' => 'verificarNascimento(this)',
                'class' => 'block w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => '00/00/0000',
            ]
        ]);
        $this->add([
            'name'       => 'patins',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Patins nº * ',
            ],
            'attributes' => [
                'id'       => 'patins',
                'maxlength' => '5',
                'onblur' => 'verificarPatins(this)',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'tamanho (número) dos patins do atleta',
            ],
        ]);
        $this->add([
            'name'       => 'turma01',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Turma 1 * ',
                'empty_option' => '-- Selecione uma turma --', // Opção vazia (opcional)
                'value_options' => [], // As opções serão preenchidas aqui
            ],
            'attributes' => [
                'id'       => 'turma01',
                'maxlength' => '20',
                'class' => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500', // CSS class
            ],
        ]);

        $this->add([
            'name'       => 'turma02',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Turma 2 * ',
                'empty_option' => '-- Selecione uma turma --', // Opção vazia (opcional)
                'value_options' => [], // As opções serão preenchidas aqui
            ],
            'attributes' => [
                'id'       => 'turma02',
                'maxlength' => '20',
                'class' => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
            ],
        ]);
        $this->add([
            'name'       => 'turma03',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Turma 3 * ',
                'empty_option' => '-- Selecione uma turma --', // Opção vazia (opcional)
                'value_options' => [], // As opções serão preenchidas aqui
            ],
            'attributes' => [
                'id'       => 'turma03',
                'maxlength' => '20',
                'class' => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
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
                    ['name' => StripTags::class]
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
                            ]
                        ]
                    ]
                ]
            ],
            'nascimento' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => Digits::class] // Remove parênteses, espaços e traços
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 8,
                            'max' => 8,
                            'messages' => [
                                StringLength::TOO_SHORT => '8',
                                StringLength::TOO_LONG  => '8',
                            ],
                        ]
                    ],
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'Informe a data de nascimento do atleta',
                            ]
                        ]
                    ]
                ]
            ],
            'patins'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 2,
                            'max' => 5,
                            'messages' => [
                                StringLength::TOO_SHORT => 'este campo aceita 2 números e mais 2 dígitos',
                                StringLength::TOO_LONG  => 'este campo aceita 2 números e mais 2 dígitos',
                            ],
                        ],
                    ],
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                // Substitui a mensagem padrão para campo vazio
                                Validator\NotEmpty::IS_EMPTY => 'informe o tamanho dos seus patins',
                            ],
                        ],
                    ],
                ],
            ],
            'titular'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'escolha um nome da lista',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 7,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => 'escolha um nome da lista',
                                StringLength::TOO_LONG  => 'escolha um nome da lista',
                            ],
                        ],
                    ],
                ],
            ],
            'turma01'  => [
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
            ],
            'turma02'  => [
                'required'   => FALSE,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
            ],
            'turma03'  => [
                'required'   => FALSE,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
            ],
        ];
    }
}
