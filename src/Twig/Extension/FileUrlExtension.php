<?php

namespace App\Twig\Extension;

use Twig\Attribute\AsTwigFilter;

final readonly class FileUrlExtension
{
    #[AsTwigFilter('file_url')]
    public function getFileUrl(?string $fileName): string
    {
        if (!$fileName) {
            return '#';
        }

        return '/uploads/' . $fileName;
    }
}
