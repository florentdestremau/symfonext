<?php

namespace App\Twig\Components;

use App\Dto\BriefcaseDto;
use App\Entity\Briefcase;
use App\Entity\Note;
use App\Form\BriefcaseLiveType;
use App\Service\SubmitWithRequestFormTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use Webmozart\Assert\Assert;

#[AsLiveComponent]
final class BriefcaseForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;
    use SubmitWithRequestFormTrait;

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory,
    ) {
        $this->submitForm();
        $dto = $this->getForm()->getData();
        Assert::isInstanceOf($dto, BriefcaseDto::class);

        $briefcase = new Briefcase();
        $briefcase->setName($dto->name);
        $entityManager->persist($briefcase);

        foreach ($dto->notes as $dtoNote) {
            $note = new Note();
            $note->setTitle($dtoNote->title);
            $note->setBriefcase($briefcase);

            if ($dtoNote->file) {
                $uniqueName = uniqid() . '.' . $dtoNote->file->guessExtension();
                $dtoNote->file->move($uploadDirectory, $uniqueName);
                $note->setFileName($uniqueName);
            }

            $entityManager->persist($note);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_briefcase_index', [], Response::HTTP_SEE_OTHER);
    }

    private function submitForm(bool $validateAll = true): void
    {
        $this->submitFormWithRequest($validateAll, $this->requestStack->getCurrentRequest());
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(BriefcaseLiveType::class);
    }
}
