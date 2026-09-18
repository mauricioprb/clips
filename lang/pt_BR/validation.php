<?php

declare(strict_types=1);

return [
    'after_or_equal' => 'O campo :attribute precisa ser uma data igual ou posterior a :date.',
    'array' => 'O campo :attribute precisa ser uma lista.',
    'between' => [
        'numeric' => 'O campo :attribute precisa estar entre :min e :max.',
    ],
    'confirmed' => 'A confirmação de :attribute não confere.',
    'current_password' => 'A senha atual está incorreta.',
    'date_format' => 'O campo :attribute não está no formato :format.',
    'distinct' => 'O campo :attribute tem um valor repetido.',
    'email' => 'O campo :attribute precisa ser um e-mail válido.',
    'enum' => 'O valor escolhido para :attribute é inválido.',
    'gt' => [
        'numeric' => 'O campo :attribute precisa ser maior que :value.',
    ],
    'integer' => 'O campo :attribute precisa ser um número inteiro.',
    'max' => [
        'array' => 'O campo :attribute não pode ter mais que :max itens.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'min' => [
        'array' => 'O campo :attribute precisa ter pelo menos :min item.',
        'numeric' => 'O campo :attribute precisa ser pelo menos :min.',
        'string' => 'O campo :attribute precisa ter pelo menos :min caracteres.',
    ],
    'multiple_of' => 'O campo :attribute precisa ser múltiplo de :value.',
    'numeric' => 'O campo :attribute precisa ser um número.',
    'password' => [
        'letters' => 'A senha precisa ter pelo menos uma letra.',
        'mixed' => 'A senha precisa ter letras maiúsculas e minúsculas.',
        'numbers' => 'A senha precisa ter pelo menos um número.',
        'symbols' => 'A senha precisa ter pelo menos um símbolo.',
        'uncompromised' => 'Esta senha apareceu em um vazamento de dados. Escolha outra.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'required_unless' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute precisa ser um texto.',
    'unique' => 'Este :attribute já está em uso.',

    'custom' => [
        'start_minute' => [
            'overlap' => 'Este horário se sobrepõe a outro lançamento do mesmo dia.',
        ],
        'end_minute' => [
            'gt' => 'O fim precisa ser depois do início.',
        ],
    ],

    'attributes' => [
        'advisor_name' => 'orientador',
        'current_password' => 'senha atual',
        'date' => 'data',
        'description' => 'descrição',
        'email' => 'e-mail',
        'end_minute' => 'fim',
        'laboratories' => 'laboratórios',
        'laboratories.*' => 'laboratório',
        'name' => 'nome',
        'password' => 'senha',
        'password_confirmation' => 'confirmação de senha',
        'priority' => 'prioridade',
        'recurrence' => 'repetição',
        'scholarship_name' => 'bolsa',
        'start_minute' => 'início',
        'valid_from' => 'início da validade',
        'valid_until' => 'fim da validade',
        'weekday' => 'dia da semana',
        'weekly_workload_hours' => 'carga horária semanal',
    ],
];
