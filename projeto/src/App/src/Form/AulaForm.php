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

class AulaForm extends Form implements InputFilterProviderInterface
{
    public function __construct($name = null)
    {
        parent::__construct('aula');


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
                'class'  => 'w-full indent-1 border rounded border-gray-200 bg-gray-100 outline-none border-blue-500',
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
                'maxlength' => '10',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um início da lista ou crie novo',
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
                'maxlength' => '10',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um término da lista ou crie novo',
            ],
        ]);

        $this->add([
            'name'       => 'data',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Data * ',
            ],
            'attributes' => [
                'list'  =>  'lista-datas',
                'id'       => 'data',
                'maxlength' => '10',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize uma data da lista ou crie nova',
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
                'placeholder' => 'utilize um monitor da lista ou crie novo',
            ],
        ]);
        $this->add([
            'name'       => 'monitor1',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Monitor1 * ',
            ],
            'attributes' => [
                'list'  =>  'lista-monitores1',
                'id'       => 'monitor1',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um monitor da lista ou crie novo',
            ],
        ]);
        $this->add([
            'name'       => 'monitor2',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Monitor2 * ',
            ],
            'attributes' => [
                'list'  =>  'lista-monitores2',
                'id'       => 'monitor2',
                'maxlength' => '100',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um monitor da lista ou crie novo',
            ],
        ]);
        $this->add([
            'name'       => 'presente',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Presentes	 * ',
            ],
            'attributes' => [
                'list'  =>  'lista-presentes',
                'id'       => 'presente',
                'maxlength' => '500',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize os presentes da lista ou altere como necessário',
            ],
        ]);
        $this->add([
            'name'       => 'ausente',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Ausente* ',
            ],
            'attributes' => [
                'list'  =>  'lista-ausentes',
                'id'       => 'ausente',
                'maxlength' => '500',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize um ausente da lista ou crie novo',
            ],
        ]);
        $this->add([
            'name'       => 'nota',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'Nota * ',
            ],
            'attributes' => [
                'list'  =>  'lista-notas',
                'id'       => 'nota',
                'maxlength' => '500',
                'class'  => 'w-full indent-1 border rounded border-gray-200 focus:bg-gray-100 outline-none focus:border-blue-500',
                'placeholder' => 'utilize a nota da lista ou crie nova',
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nível da aula',
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
                                StringLength::TOO_SHORT => 'O início é uma hora no formato 00:00hs',
                                StringLength::TOO_LONG  => 'O início é uma hora no formato 00:00hs',
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
                                StringLength::TOO_SHORT => 'O término é uma hora no formato 00:00hs',
                                StringLength::TOO_LONG  => 'O término é uma hora no formato 00:00hs',
                            ],
                        ],
                    ],
                ],
            ],
            'data'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe a data  da aula',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 10,
                            'max' => 10,
                            'messages' => [
                                StringLength::TOO_SHORT => 'A data deve ter 10 dígitos',
                                StringLength::TOO_LONG  => 'A data deve ter 10 dígitos',
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nome do monitor',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 4,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O nome do monitor deve ter mais de 4 letras',
                                StringLength::TOO_LONG  => 'O nome do monitor deve ter menos de 101 letras',
                            ],
                        ],
                    ],
                ],
            ],
            'monitor1'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nome do monitor',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 4,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O nome do monitor deve ter mais de 4 letras',
                                StringLength::TOO_LONG  => 'O nome do monitor deve ter menos de 101 letras',
                            ],
                        ],
                    ],
                ],
            ],
            'monitor2'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'Informe o nome do monitor',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 4,
                            'max' => 100,
                            'messages' => [
                                StringLength::TOO_SHORT => 'O nome do monitor deve ter mais de 4 letras',
                                StringLength::TOO_LONG  => 'O nome do monitor deve ter menos de 101 letras',
                            ],
                        ],
                    ],
                ],
            ],
            'presentes'  => [
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
                                Validator\NotEmpty::IS_EMPTY => 'utilize a lista ou modifique conforme necessário',
                            ],
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 5,
                            'max' => 500,
                            'messages' => [
                                StringLength::TOO_SHORT => 'a lista de presentes é indispensável',
                                StringLength::TOO_LONG  => 'a lista de presentes tem no máximo 500 dígitos',
                            ],
                        ],
                    ],
                ],
            ],
            'ausentes'  => [
                'required'   => false,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'max' => 500,
                            'messages' => [
                                StringLength::TOO_LONG  => 'a lista de ausentes tem no máximo 500 dígitos',
                            ],
                        ],
                    ],
                ],
            ],
            'nota'  => [
                'required'   => false,
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'max' => 500,
                            'messages' => [
                                StringLength::TOO_LONG  => 'a noto tem no máximo 500 dígitos',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
