<?php

namespace App\Form;

use App\Entity\Subject;
use App\Helper\FormatHelper\StreamToFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('contentFile', FileType::class, [
                'label' => 'Subject content file (TXT or PDF file)',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/plain',
                            'text/x-c',
                            'application/pdf'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid TXT/PDF file!',
                    ])
                ],
                'data_class' => null, // set data_class to null to avoid the "resource" error
//                'modelTransformer' => new StreamToFileTransformer() // add the data transformer
            ])
            ->add('subjectContent')
            ->add('subjectRequirements')
        ;

//        if ($options['edit'] == true) {
//            $builder
//            ->get('contentFile')
//            ->addViewTransformer(
//                $options['data'] ? new StreamToFileTransformer() : null
//            );
//        }

        // add a hidden field to store the subject ID for editing
        $builder->add('id', HiddenType::class, [
            'mapped' => false, // this field is not mapped to the object
            'data' => $options['subject_id'], // pass the subject ID to the form
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subject::class,
            'subject_id' => null, // the subject ID defaults to null
            'edit' => false, // by default, the form is used for creating a subject,
            'model_transformer' => new StreamToFileTransformer()
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'subject';
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }
}
