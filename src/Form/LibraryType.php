<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/11/2019
 * Time: 13:45
 */

namespace Kookaburra\Library\Form;

use App\Entity\Department;
use App\Entity\Space;
use App\Form\Type\FilePathType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\Library;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LibraryType
 * @package Kookaburra\Library\Form
 */
class LibraryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selectLibrary', HeaderType::class,
                [
                    'label' => 'Library {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getName()],
                ]
            )
            ->add('workingOn', EntityType::class,
                [
                    'mapped' => false,
                    'label' => 'Library',
                    'class' => Library::class,
                    'data' => $options['data']->getId(),
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'submit_on_change' => true,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->orderBy('l.name')
                        ;
                    },
                    'help' => 'This selection sets the the current Library with which yoiu are working, and also allows you to change the settings for this library.',
                ]
            )
            ->add('librarySettings', HeaderType::class,
                [
                    'label' => 'Library Settings',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Library Name',
                    'help' => 'Must be unique',
                ]
            )
            ->add('abbr', TextType::class,
                [
                    'label' => 'Library Abbreviation',
                    'help' => 'Must be unique',
                ]
            )
            ->add('borrowLimit', IntegerType::class,
                [
                    'label' => 'Borrowing Limit',
                    'help' => 'The maximum number of items a borrower can have on loan.',
                    'attr' => [
                        'max' => 99,
                    ]
                ]
            )
            ->add('department', EntityType::class,
                [
                    'label' => 'Department',
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'data' => $options['data']->getDepartment() ? $options['data']->getDepartment()->getId() : 0,
                    'class' => Department::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->orderBy('d.name');
                    },
                ]
            )
            ->add('facility', EntityType::class,
                [
                    'label' => 'Facility',
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'help' => 'The storage location when the item is not in use.',
                    'data' => $options['data']->getFacility() ? $options['data']->getFacility()->getId() : 0,
                    'class' => Space::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.name');
                    },
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('lendingPeriod', IntegerType::class,
                [
                    'label' => 'Default Lending Period',
                    'help' => 'in days',
                ]
            )
            ->add('bgColour', ColorType::class,
                [
                    'label' => 'Background Colour',
                    'help' => '<a class="text-blue-700 underline" href="https://www.w3schools.com/colors/default.asp" target="_blank">https://www.w3schools.com/colors/default.asp</a>',
                ]
            )
            ->add('bgImage', FilePathType::class,
                [
                    'label' => 'Background Image',
                    'file_prefix' => 'lib_bg_',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => '<span class="far fa-save fa-fw"></span>',
                    'attr' => [
                        'style' => 'float: right;',
                        'title' => 'Submit',
                        'class' => 'btn-gibbon',
                    ],
                    'translation_domain' => 'messages',
                ]
            )
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Library',
                'data_class' => Library::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}