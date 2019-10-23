<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 15:58
 */

namespace Kookaburra\Library\Form;


use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Validator\UniqueIdentifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DuplicateCopyIdentifierType extends AbstractType
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
            ->add('identifiers', HeaderType::class,
                [
                    'label' => 'Item Identifiers',
                ]
            );
        for($x=1; $x<=$options['copies']; $x++){
            $builder->add('identifier'.$x, TextType::class,
                [
                    'label' => 'Item Identifier',
                    'label_translation_parameters' => ['count' => $x],
                    'help' => 'Must be Unique',
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(),
                        new UniqueIdentifier(),
                    ],
                ]
            );
        }

        $builder->add('submit', SubmitType::class,
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
            'translation_domain' => 'Library',
            'data_class' => LibraryItem::class,
        ]);
        $resolver->setRequired([
            'copies',
        ]);
        $resolver->setAllowedTypes('copies', ['integer']);
    }
}