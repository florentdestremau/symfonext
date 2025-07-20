<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class NoteDto
{
    public ?string $title = null;
    #[Assert\File(maxSize: '100K')]
    public ?UploadedFile $file = null;
}
