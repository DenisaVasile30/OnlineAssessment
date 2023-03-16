<?php

namespace App\Form;

use App\Entity\Assessment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessmentFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void
    {
        $assignedGroups = (explode(', ', $options['teacherAssignedGroups'][0]));;
        $builder
            ->add('description')
            ->add('assigneeGroup', ChoiceType::class, [
                'label' => 'Assignee Groups',
                'required' => true,
                'choices' => array_combine($assignedGroups, $assignedGroups),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('subjectList', ChoiceType::class, [
                'label' => 'Subject/s',
                'required' => true,
                'choices' => array_combine($options['subjects'], $options['subjects']),
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('startAt')
            ->add('endAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assessment::class,
            'teacherAssignedGroups' => null,
            'subjects' => null,
        ]);
    }
}
