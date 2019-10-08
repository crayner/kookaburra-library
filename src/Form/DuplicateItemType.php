<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 14:10
 */

namespace Kookaburra\Library\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryType;
use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DuplicateItemType
 * @package Kookaburra\Library\Form
 */
class DuplicateItemType extends AbstractType
{
    /**
     * @var LibraryManager
     */
    private $libraryManager;

    /**
     * DuplicateItemType constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', HeaderType::class,
                [
                    'label' => 'Quantity',
                    'help' => $this->libraryManager->isGenerateIdentifier() ? 'The system will create unique identifiers for each new item.' : 'Manual entry of item identifiers is required.',
                ]
            )
            ->add('libraryType', EntityType::class,
                [
                    'class' => LibraryType::class,
                    'label' => 'Item Type',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('lt')
                            ->orderBy('lt.name');
                    },
                    'attr' => [
                        'disabled' => 'disabled',
                    ],
                    'required' => false,
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'attr' => [
                        'disabled' => 'disabled',
                    ],
                    'required' => false,
                ]
            )
            ->add('identifier', TextType::class,
                [
                    'label' => 'Identifier',
                    'help' => 'Unique key for this item on the system.',
                    'attr' => [
                        'disabled' => 'disabled',
                    ],
                    'required' => false,
                ]
            )
            ->add('producer', TextType::class,
                [
                    'label' => 'Author/Brand',
                    'attr' => [
                        'disabled' => 'disabled',
                    ],
                    'required' => false,
                ]
            )
            ->add('copies', IntegerType::class,
                [
                    'label' => 'Number of Copies',
                    'data' => 1,
                    'help' => 'How many copies do you want to make of this item?',
                    'attr' => [
                        'min' => 1,
                        'max' => $this->libraryManager->getMaximumCopies(),
                    ],
                    'mapped' => false
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            )
        ;
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'data_class' => LibraryItem::class,
        ]);
    }
}