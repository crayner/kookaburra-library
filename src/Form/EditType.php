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

use App\Form\Type\EntityType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Form\Subscriber\LibraryItemSubscriber;
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
            ->add('itemIdentifierLabel', HeaderType::class,
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
                    //'data' => $options['data']->getLibrary() !== null ? $options['data']->getLibrary()->getId() : null,
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
            ->add('itemType', EnumType::class,
                [
                    'label' => 'Library Item Type',
                    'placeholder' => 'Please select...',
                    'data' => $options['data']->getItemType(),
                    'choice_list_prefix' => false,
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
            'translation_domain' => 'Library',
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