<?php

namespace App\Twig\Components;

use App\Dto\NoteDto;
use App\Entity\Note;
use App\Form\NoteLiveFormType;
use App\Service\SubmitWithRequestFormTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Webmozart\Assert\Assert;

#[AsLiveComponent]
final class LiveNoteForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use SubmitWithRequestFormTrait;

    private function submitForm(bool $validateAll = true): void
    {
        $this->submitFormWithRequest($validateAll, $this->requestStack->getCurrentRequest());
    }

    #[LiveAction]
    public function save(
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory,
    ): RedirectResponse {
        $this->submitForm();
        $dto = $this->getForm()->getData();

        Assert::isInstanceOf($dto, NoteDto::class);

        // Create a new Note entity from the DTO
        $note = new Note();
        $note->setTitle($dto->title);

        // Handle file upload
        $file = $dto->file;

        if ($file instanceof UploadedFile) {
            $uniqueName = uniqid() . '.' . $file->guessExtension();
            $file->move($uploadDirectory, $uniqueName);
            $note->setFileName($uniqueName);
        }

        $entityManager->persist($note);
        $entityManager->flush();

        return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NoteLiveFormType::class);
    }
}
