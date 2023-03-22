<?php

namespace App\Form;

use App\Entity\AssignedSubjects;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignedSubjectsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        for ($i = 1; $i <= $options['data']['sectionsNo']; $i++) {
            $builder
                ->add('subjectList' . $i, ChoiceType::class, [
                    'label' => 'Requirement ' . $i,
                    'required' => true,
                    'choices' => array_combine($options['data']['subjects'], $options['data']['subjects']),
                    'expanded' => false,
                    'multiple' => true,
                ])
            ;
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'subjects' => null,
            'sectionsNo' => null,
        ]);
    }
}
