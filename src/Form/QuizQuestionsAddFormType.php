<?php

namespace App\Form;

use App\Entity\QuizQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class QuizQuestionsAddFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('contentFile', FileType::class, [
//                'label' => 'Upload questions from txt file here',
//                'mapped' => true,
//                'required' => false,
//                'constraints' => [
//                    new File([
//                        'maxSize' => '1024k',
//                        'mimeTypes' => [
//                            'text/plain',
//                            'text/x-c',
//                        ],
//                        'mimeTypesMessage' => 'Please upload a valid TXT file!',
//                    ])
//                ]])
            ->add('category')
            ->add('optionalDescription')
            ->add('questionContent')
            ->add('choiceA')
            ->add('choiceB')
            ->add('choiceC')
            ->add('choiceD')
            ->add('correctAnswer')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizQuestion::class,
        ]);
    }
}