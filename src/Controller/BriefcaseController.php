<?php

namespace App\Controller;

use App\Entity\Briefcase;
use App\Form\BriefcaseType;
use App\Repository\BriefcaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/briefcase')]
final class BriefcaseController extends AbstractController
{
    #[Route(name: 'app_briefcase_index', methods: ['GET'])]
    public function index(BriefcaseRepository $briefcaseRepository): Response
    {
        return $this->render('briefcase/index.html.twig', [
            'briefcases' => $briefcaseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_briefcase_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $briefcase = new Briefcase();
        $form = $this->createForm(BriefcaseType::class, $briefcase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($briefcase);
            $entityManager->flush();

            return $this->redirectToRoute('app_briefcase_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('briefcase/new.html.twig', [
            'briefcase' => $briefcase,
            'form' => $form,
        ]);
    }

    #[Route('/new-live')]
    public function newLive()
    {
        return $this->render('briefcase/new-live.html.twig');
    }

    #[Route('/{id}', name: 'app_briefcase_show', methods: ['GET'])]
    public function show(Briefcase $briefcase): Response
    {
        return $this->render('briefcase/show.html.twig', [
            'briefcase' => $briefcase,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_briefcase_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Briefcase $briefcase, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BriefcaseType::class, $briefcase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_briefcase_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('briefcase/edit.html.twig', [
            'briefcase' => $briefcase,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_briefcase_delete', methods: ['POST'])]
    public function delete(Request $request, Briefcase $briefcase, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$briefcase->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($briefcase);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_briefcase_index', [], Response::HTTP_SEE_OTHER);
    }
}
