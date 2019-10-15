<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 20:04
 */

namespace Kookaburra\Library\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryType;
use Kookaburra\Library\Form\Subscriber\LibraryItemSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditType
 * @package Kookaburra\Library\Form
 */
class EditType extends AbstractType
{
    /**
     * @var LibraryItemSubscriber
     */
    private $subscriber;

    /**
     * EditType constructor.
     * @param LibraryItemSubscriber $subscriber
     */
    public function __construct(LibraryItemSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }


    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libraryItemType', HeaderType::class,
                [
                    'label' => 'Library Item Type',
                    'help' => 'Item Identifier {identifier}',
                    'help_translation_parameters' => ['{identifier}' => $options['data']->getIdentifier()],
                    'panel' => 'Catalogue',
                ]
            )
            ->add('library', EntityType::class,
                [
                    'class' => Library::class,
                    'choice_label' => 'name',
                    'label' => 'Library',
                    'placeholder' => 'Please select...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->where('l.active = :true')
                            ->orderBy('l.name')
                            ->setParameter('true',true);
                    },
                    'on_change' => 'selectLibraryAndType',
                    'panel' => 'Catalogue',
                ]
            )
            ->add('libraryType', EntityType::class,
                [
                    'class' => LibraryType::class,
                    'choice_label' => 'name',
                    'label' => 'Library Item Type',
                    'placeholder' => 'Please select...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('lt')
                            ->where('lt.active = :yes')
                            ->orderBy('lt.name')
                            ->setParameter('yes','Y');
                    },
                    'on_change' => 'selectLibraryAndType',
                    'panel' => 'Catalogue',
                ]
            )
        ;
        $this->subscriber->setOptions($options);
        $builder->addEventSubscriber($this->subscriber);
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
            'allow_extra_fields' => true,
        ]);
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