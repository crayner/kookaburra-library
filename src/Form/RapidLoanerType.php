<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 13/11/2019
 * Time: 15:53
 */

namespace Kookaburra\Library\Form;

use App\Entity\Person;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactCollectionType;
use App\Form\Type\ReactFormType;
use App\Util\ImageHelper;
use Kookaburra\Library\Entity\RapidLoan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RapidLoanerType
 * @package Kookaburra\Library\Form
 */
class RapidLoanerType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $personAttr = [];
        $person = $options['data']->getPerson();
        if (null !== $person) {
            $personAttr['data-photo'] = ImageHelper::getAbsoluteImageURL('File', $person->getImage240(true));
            $personAttr['data-name'] = $person->formatName(['informal' => true]);
        }
        $builder
            ->add('loanHeader', HeaderType::class,
                [
                    'label' => 'Loan/Return Manager',
                ]
            )
            ->add('search', TextType::class,
                [
                    'label' => 'Item/Person Search',
                    'on_blur' => 'submitForm',
                    'on_key_press' => 'submitOnEnter',
                ]
            )
            ->add('items', ReactCollectionType::class,
                [
                    'label' => false,
                    'allow_delete' => true,
                    'allow_add' => false,
                    'element_delete_route' => 'doNothing',
                    'prototype' => true,
                    'entry_type' => RapidLoanItemType::class,
                    'row_style' => 'hidden',
                    'header_row' => false,
                ]
            )
            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                    'attr' => $personAttr,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Confirm',
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
                'data_class' => RapidLoan::class,
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