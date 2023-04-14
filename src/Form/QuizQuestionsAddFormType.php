<?php

namespace App\Form;

use App\Entity\QuizQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class QuizQuestionsAddFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category')
            ->add('optionalDescription')
            ->add('questionContent')
            ->add('choiceA')
            ->add('choiceB')
            ->add('choiceC')
            ->add('choiceD')
            ->add('correctAnswer')
        ;

        $builder->add('id', HiddenType::class, [
            'mapped' => false, // this field is not mapped to the object
            'data' => $options['question_id'], // pass the subject ID to the form
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizQuestion::class,
            'question_id' => null, // the subject ID defaults to null
            'edit' => false, // by default, the form is used for creating a subject,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'question';
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }
}
