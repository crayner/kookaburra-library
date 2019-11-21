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

use Kookaburra\UserAdmin\Entity\Person;
use App\Entity\Space;
use App\Form\Transform\EntityToStringTransformer;
use App\Form\Type\EnumType;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\Library;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CatalogueSearchType
 * @package Kookaburra\Form
 */
class CatalogueSearchType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('library', EntityType::class,
                [
                    'label' => 'Library',
                    'required' => true,
                    'help' => 'Clear this value to show all items from all libraries.',
                    'class' => Library::class,
                    'placeholder' => false,
                    'data' => $options['data']->getLibrary() instanceof Library ? $options['data']->getLibrary()->getId() : null,
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->where('l.active = :true')
                            ->setParameter('true', true)
                            ->orderBy('l.name')
                        ;
                    },
                ]
            )
            ->add('search', TextType::class,
                [
                    'label' => 'ID/Name/Producer',
                    'required' => false,
                ]
            )
            ->add('type', EnumType::class,
                [
                    'label' => 'Item Type',
                    'choice_list_prefix' => false,
                    'placeholder' => ' ',
                    'required' => false,
                ]
            )
            ->add('location', EntityType::class,
                [
                    'label' => 'Location',
                    'class' => Space::class,
                    'required' => false,
                    'choice_label' => 'name',
                    'placeholder' => '',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.name', 'ASC');
                    },
                ]
            )
            ->add('status', EnumType::class,
                [
                    'label' => 'Status',
                    'placeholder' => '',
                    'choice_list_prefix' => false,
                    'required' => false,
                ]
            )
            ->add('person', ChoiceType::class,
                [
                    'label' => 'Owner/User',
                    'required' => false,
                    'placeholder' => '',
                    'choices' => ProviderFactory::create(Person::class)->findAllFullList(),
                    'choice_translation_domain' => false,
                ]
            )
            ->add('searchFields', TextType::class,
                [
                    'label' => 'Type Specific Fields',
                    'required' => false,
                    'help' => "For example, a computer's MAC address or a book's ISBN.",
                ]
            )
            ->add('export', SubmitType::class,
                [
                    'label' => '<span class="fas fa-file-export fa-fw"></span>',
                    'attr' => [
                        'style' => 'float: right;',
                        'title' => 'Export Summary',
                        'class' => 'btn-gibbon',
                    ],
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

        $builder->get('person')->addModelTransformer(new EntityToStringTransformer(ProviderFactory::getEntityManager(), ['class' => Person::class, 'multiple' => false]));

    }

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