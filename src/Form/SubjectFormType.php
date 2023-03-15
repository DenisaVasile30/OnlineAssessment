<?php

namespace App\Form;

use App\Entity\Subject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SubjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('subject', ChoiceType::class, [
                'choices'  => [
                    'SDD' => 'SDD',
                    'POO' => 'POO',
                ]]
            )
            ->add('content', FileType::class, [
                'label' => 'Subject content file (TXT or PDF file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/txt',
                            'application/pdf'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid TXT/PDF file!',
                    ])
                ]])
            ->add('subjectContent')
            ->add('subjectRequirements')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subject::class,
        ]);
    }
}
