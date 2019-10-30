<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 29/10/2019
 * Time: 11:36
 */

namespace Kookaburra\Library\Form;

use App\Entity\Person;
use App\Form\Transform\EntityToStringTransformer;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactSubFormType;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryReturnAction;
use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReturnActionType
 * @package Kookaburra\Library\Form
 */
class ReturnActionType extends AbstractType
{
    /**
     * @var LibraryManager
     */
    private $libraryManager;

    /**
     * ItemActionType constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('onReturnHeader', HeaderType::class,
                [
                    'label' => 'On Return',
                ]
            )->add('returnAction', EnumType::class,
                [
                    'label' => 'Action',
                    'help' => 'What to do when item is next returned.',
                    'placeholder' => ' ',
                    'choice_list_prefix' => false,
                ]
            )->add('actionBy', ChoiceType::class,
                [
                    'label' => 'Responsible User',
                    'choices' => $this->libraryManager->getBorrowerList(),
                    'placeholder' => ' ',
                    'choice_translation_domain' => false,
                    'help' => 'Who will be responsible for the future status?'
                ]
            )->add('return', SubmitType::class,
                [
                    'label' => 'On Return',
                ]
            )
        ;
        $builder->get('actionBy')->addModelTransformer(new EntityToStringTransformer(ProviderFactory::getEntityManager(), ['class' => Person::class, 'multiple' => false]));
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
                'data_class' => LibraryReturnAction::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactSubFormType::class;
    }
}