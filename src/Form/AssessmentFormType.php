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
            ->add('subjectList', ChoiceType::class, [
                'label' => 'Subject/s',
                'required' => true,
                'choices' => array_combine($options['subjects'], $options['subjects']),
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('startAt')
            ->add('endAt')
            ->add('timeLimit')
            ->add('timeUnit', ChoiceType::class, [
                'choices'  => [
                    'no limit' => 'no time limit',
                    'minutes' => 'minutes',
                    'hours' => 'hours',
                ],
            ])
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
