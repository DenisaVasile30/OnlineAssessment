<?php

namespace App\Form;

use App\Entity\CreatedQuiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StartQuizFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('next', SubmitType::class)
            ->add('submit', SubmitType::class);
//        $questions = $options['data']['questions'];
//        foreach ($questions as $question) {
//            $builder
//                ->add('questionContent' . $question->getId(), TextType::class, [
//                    'attr' => [
//                        'readonly' => true,
//                    ],
//                    'data' => $question->getQuestionContent(),
//                    'mapped' => false
//                ])
//                ->add('answerOptions' . $question->getId(), ChoiceType::class, [
//                    'choices' => [
//                        'choiceA' . $question->getId() => $question->getChoiceA(),
//                        'choiceB' . $question->getId() => $question->getChoiceB(),
//                        'choiceC' . $question->getId() => $question->getChoiceC(),
//                        'choiceD' . $question->getId() => $question->getChoiceD(),
//                    ],
//                    'expanded' => true,
//                ])
//            ;
//        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'questions' => null
        ]);
    }
}
