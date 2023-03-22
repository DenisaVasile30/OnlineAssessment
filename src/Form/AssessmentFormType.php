<?php

namespace App\Form;

use App\Entity\Assessment;
use App\Entity\AssignedSubjects;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessmentFormType extends AbstractType
{
    private array $subjects = [];

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void
    {
//        dd($options['sectionsNo']);
        $assignedGroups = (explode(', ', $options['teacherAssignedGroups'][0]));
        $this->subjects = $options['subjects'];
        $builder
            ->add('description')
            ->add('assigneeGroup', ChoiceType::class, [
                'label' => 'Assignee Groups',
                'required' => true,
                'choices' => array_combine($assignedGroups, $assignedGroups),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('requirementsNo', ChoiceType::class, [
                'choices'  => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                    6 => 6,
                    7 => 7,
                    8 => 8,
                    9 => 9,
                    10 => 10,
                ],
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
//            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assessment::class,
            'teacherAssignedGroups' => null,
            'subjects' => null,
            'sectionsNo' => 1
        ]);
    }
}
