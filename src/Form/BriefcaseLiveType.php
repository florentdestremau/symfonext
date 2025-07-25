<?php

namespace App\Form;

use App\Dto\BriefcaseDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class BriefcaseLiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name')
            ->add('notes', LiveCollectionType::class, [
                'entry_type'   => NoteLiveFormType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BriefcaseDto::class,
        ]);
    }
}
