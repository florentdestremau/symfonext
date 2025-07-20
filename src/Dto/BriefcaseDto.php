<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BriefcaseDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public ?string $name = null;

    /**
     * @var iterable<NoteDto>
     */
    #[Assert\Valid]
    public iterable $notes = [];
}
