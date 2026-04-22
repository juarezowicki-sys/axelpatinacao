<?php

declare(strict_types=1);

namespace App\Form;

use Laminas\Db\Sql\Sql;
use Laminas\Validator\Callback;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\StringLength;
use Laminas\Filter\Digits;
use Laminas\Validator\Regex;
use Laminas\Validator;

class TurmaForm extends Form implements InputFilterProviderInterface
{
    protected $adapter;
    protected $id;

    public function __construct($adapter, $name = 'turma', $id = null)
    {
        parent::__construct('turma');
        parent::__construct($name);
        $this->adapter = $adapter;
        $this->id = $id;

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name'       => 'nome',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Nome da turma  * ',
            ],
            'attributes' => [
                'list'  =>  'lista-nomes',
                'id'       => 'nome',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-300 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'crie um novo nome - que não exista na lista',
            ],
        ]);
        $this->add([
            'name'       => 'nivel',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Nível *',
            ],
            'attributes' => [
                'list'  =>  'lista-niveis',
                'id'       => 'nivel',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-300 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um nível da lista ou crie novo',
            ],
        ]);
        $this->add([
            'name'       => 'local',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'local das aulas * ',
            ],
            'attributes' => [
                'list'  =>  'lista-locais',
                'maxlength' => '200',
                'id'       => 'local',
                'class' => 'block w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um local da lista ou crie novo',
            ],
        ]);

        $this->add([
            'name'       => 'monitor',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Monitor * ',
            ],
            'attributes' => [
                'list'  =>  'lista-monitores',
                'id'       => 'monitor',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um monitor da lista',
            ],
        ]);
        $this->add([
            'name'       => 'dia',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'dia da semana * ',
            ],
            'attributes' => [
                'list'  =>  'lista-dias',
                'id'       => 'dia',
                'maxlength' => '20',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um dia da lista ou crie novo',
            ],
        ]);
        $this->add([
            'name'       => 'inicio',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Início * ',
            ],
            'attributes' => [
                'list'  =>  'lista-inicios',
                'id'       => 'inicio',
                'maxlength' => '7',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um horário da lista ou crie um novo no mesmo formato',
            ],
        ]);
        $this->add([
            'name'       => 'termino',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Término * ',
            ],
            'attributes' => [
                'list'  =>  'lista-terminos',
                'id'       => 'termino',
                'maxlength' => '7',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um horário da lista ou crie um novo no mesmo formato',
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
        // Criamos uma variável local para ser usada no 'use'
        $idAtual = $this->id;
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
                                Validator\NotEmpty::IS_EMPTY => 'crie um novo nome - não utilize um da lista',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 4,
                            'max' => 30,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O nome deve ter mais de 3 letras',
                                StringLength::TOO_LONG  => 'O nome deve ter menos de 30 letras',
                            ],
                        ],
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function ($value, $context) {
                                // Agora o $context['id'] vale 49!
                                $idAtual = isset($context['id']) ? (int) $context['id'] : 0;

                                $sql    = new \Laminas\Db\Sql\Sql($this->adapter);
                                $select = $sql->select('turmas');
                                $where  = new \Laminas\Db\Sql\Where();

                                $where->equalTo('nome', $value);

                                // Se o ID for 49, o SQL vai ignorar a própria turma na busca por duplicatas
                                if ($idAtual > 0) {
                                    $where->notEqualTo('id', $idAtual);
                                }

                                $select->where($where);
                                $statement = $sql->prepareStatementForSqlObject($select);
                                $results   = $statement->execute();

                                // Se o count for 0, o nome está liberado!
                                return $results->count() === 0;
                            },
                            'messages' => [
                                \Laminas\Validator\Callback::INVALID_VALUE => 'calback Crie um novo nome - não utilize um da lista',
                            ],
                        ],
                    ],
                ],

            ],
            'nivel'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nível da nova turma',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 5,
                            'max' => 70,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O nível deve ter mais de 4 letras',
                                StringLength::TOO_LONG  => 'O nível deve ter menos de 70 letras',
                            ],
                        ],
                    ],
                ],
            ],

            'local'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o local da nova turma',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 3,
                            'max' => 69,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O local deve ter mais de 2 letras',
                                StringLength::TOO_LONG  => 'O local deve ter menos de 70 letras',
                            ],
                        ],
                    ],
                ],
            ],

            'dia'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o dia da semana da nova turma',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 5,
                            'max' => 20,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O dia deve ter mais de 4 letras',
                                StringLength::TOO_LONG  => 'O dia deve ter menos de 20 letras',
                            ],
                        ],
                    ],
                ],
            ],

            'inicio'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o horário de início da nova turma',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 7,
                            'max' => 7,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O início é uma hora no formato hh:mmhs',
                                StringLength::TOO_LONG  => 'O início é uma hora no formato hh:mmhs',
                            ],
                        ],
                    ],
                ],
            ],

            'termino'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o horário de término da nova turma',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 7,
                            'max' => 7,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O término é uma hora no formato hh:mmhs',
                                StringLength::TOO_LONG  => 'O término é uma hora no formato hh:mmhs',
                            ],
                        ],
                    ],
                ],
            ],
            'monitor'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o monitor titular desta turma',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 4,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => 'escolha um monitor da lista',
                                StringLength::TOO_LONG  => 'escolha um monitor da lista',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
