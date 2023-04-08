<?php

namespace App\Form;

use App\Entity\CreatedQuiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateQuizFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $assignedGroups = $options['teacherAssignedGroups'];
        $categories = $options['quizCategories'];
        $categories[] = "";

        $builder
            ->add('description')
            ->add('assigneeGroup', ChoiceType::class, [
                'label' => 'Assignee Groups',
                'required' => true,
                'choices' => array_combine($assignedGroups, $assignedGroups),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('category', ChoiceType::class, [
                'required' => true,
                'choices' => array_combine($categories, $categories),
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('questionsNo')
            ->add('maxGrade')
            ->add('questionsSource', ChoiceType::class, [
                'choices'  => [
                    'Random from Category' => 'Random from Category',
                    'From Category' => 'From Category',
                    'Mixed' => 'Mixed',
                ],
            ])
//            ->add('questionsList')
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
            ->add('save', SubmitType::class, [
                'label' => 'Save'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreatedQuiz::class,
            'teacherAssignedGroups' => null,
            'quizCategories' => null
        ]);
    }
}
