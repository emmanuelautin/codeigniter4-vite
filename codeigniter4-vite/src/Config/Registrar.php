<?php

namespace EmmanuelAutin\CodeIgniter4Vite\Config;

class Registrar
{
    public static function Autoload(): array
    {
        return [
            'helpers' => [
                'vite',
            ],
        ];
    }
}
