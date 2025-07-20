<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class NoteLiveFormDto
{
    public ?string $title = null;
    public ?UploadedFile $file = null;
}
