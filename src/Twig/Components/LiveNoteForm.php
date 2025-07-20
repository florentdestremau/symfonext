<?php

namespace App\Twig\Components;

use App\Entity\Note;
use App\Form\NoteType;
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

#[AsLiveComponent]
final class LiveNoteForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Note $initialFormValues = null;

    #[LiveAction]
    public function save(
        EntityManagerInterface $entityManager,
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory,
    ): RedirectResponse {
        $this->submitForm();
        $note = $this->getForm()->getData();

        $file = $this->getForm()->get('file')->getData();

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
        return $this->createForm(NoteType::class, $this->initialFormValues);
    }
}
