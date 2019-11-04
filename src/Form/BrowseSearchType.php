<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 14:29
 */

namespace Kookaburra\Library\Form;

use App\Entity\Person;
use App\Entity\Space;
use App\Form\Transform\EntityToStringTransformer;
use App\Form\Type\EnumType;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\CatalogueSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BrowseSearchType
 * @package Kookaburra\Library\Form
 */
class BrowseSearchType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class,
                [
                    'label' => 'Title',
                    'required' => false,
                ]
            )
            ->add('producer', TextType::class,
                [
                    'label' => 'Author/Producer',
                    'required' => false,
                ]
            )
            ->add('searchFields', TextType::class,
                [
                    'label' => 'Type Specific Fields',
                    'required' => false,
                    'help' => "For example, a computer's MAC address or a book's ISBN.",
                ]
            )
            ->add('clear', SubmitType::class,
                [
                    'label' => '<span class="fas fa-broom fa-fw"></span>',
                    'attr' => [
                        'style' => 'float: right;',
                        'title' => 'Clear Search',
                        'class' => 'btn-gibbon',
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => '<span class="fas fa-search fa-fw"></span>',
                    'attr' => [
                        'style' => 'float: right;',
                        'title' => 'Search',
                        'class' => 'btn-gibbon',
                    ],
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
                'data_class' => CatalogueSearch::class,
                'translation_domain' => 'Library',
            ]
        );
    }
}