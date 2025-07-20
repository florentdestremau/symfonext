<?php

namespace App\Controller;

use App\Dto\NoteDto;
use App\Entity\Note;
use App\Form\NoteLiveFormType;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/note')]
final class NoteController extends AbstractController
{
    #[Route(name: 'app_note_index', methods: ['GET'])]
    public function index(NoteRepository $noteRepository): Response
    {
        return $this->render('note/index.html.twig', [
            'notes' => $noteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_note_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
    #[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory): Response
    {
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file instanceof UploadedFile) {
                $uniqueName = uniqid() . '.' . $file->guessExtension();
                $file->move($uploadDirectory, $uniqueName);
                $note->setFileName($uniqueName);
            }

            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'note' => $note,
            'form' => $form,
        ]);
    }

    #[Route('/new-dto', name: 'app_note_newdto', methods: ['GET', 'POST'])]
    public function newDto(Request $request, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory)
    {
        $dto = new NoteDto();
        $form = $this->createForm(NoteLiveFormType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = new Note()->setTitle($dto->title);

            if ($dto->file instanceof UploadedFile) {
                $uniqueName = uniqid() . '.' . $dto->file->guessExtension();
                $dto->file->move($uploadDirectory, $uniqueName);
                $note->setFileName($uniqueName);
            }

            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new-live', name: 'app_note_newlive')]
    public function newLive(): Response
    {
        return $this->render('note/new_live.html.twig');
    }

    #[Route('/{id}', name: 'app_note_show', methods: ['GET'])]
    public function show(Note $note): Response
    {
        return $this->render('note/show.html.twig', [
            'note' => $note,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/edit.html.twig', [
            'note' => $note,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_note_delete', methods: ['POST'])]
    public function delete(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
    }
}
