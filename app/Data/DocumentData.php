<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class DocumentData extends Data
{
    public function __construct(
        public string $title,
        public array|string $content,
        public ?string $instruction = null
    ) {}

    public static function rules(\Spatie\LaravelData\Support\Validation\ValidationContext $context = null): array
    {
        return [
            'title' => ['required', 'string'],
            'content' => ['required'],
            'instruction' => ['nullable', 'string'],
        ];
    }
}
