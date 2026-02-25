<?php

namespace App\Support\Enums;

enum MaterialType: string
{
    case Book = 'book';
    case Manual = 'manual';
    case Pdf = 'pdf';

    public function label(): string
    {
        return match($this) {
            self::Book => 'Libro',
            self::Manual => 'Manual',
            self::Pdf => 'PDF',
        };
    }
}
