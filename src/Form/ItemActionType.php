<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 21/10/2019
 * Time: 10:52
 */

namespace Kookaburra\Library\Form;

use Kookaburra\UserAdmin\Entity\Person;
use App\Form\Transform\EntityToStringTransformer;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ItemActionType
 * @package Kookaburra\Library\Form
 */
class ItemActionType extends AbstractType
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
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $item = $options['data'];
        if ($item->getStatus() === 'Available' && $item->isBorrowable()) {
            $builder
                ->add('borrowHeader', HeaderType::class,
                    [
                        'label' => 'Loan',
                    ]
                )->add('responsibleForStatus', ChoiceType::class,
                    [
                        'label' => 'Borrower',
                        'choices' => $this->libraryManager->getBorrowerList(),
                        'placeholder' => 'Please select...',
                        'choice_translation_domain' => false,
                        'help' => 'Who is borrowing the library item?'
                    ]
                )->add('returnExpected', DateType::class,
                    [
                        'label' => 'Expected Return Date',
                        'help' => 'return_date_help',
                        'widget' => 'text',
                        'help_attr' => ['count' => $options['data']->getLibrary()->getLendingPeriod($this->libraryManager->getBorrowPeriod($item))],
                    ]
                )->add('loan', SubmitType::class,
                    [
                        'label' => 'Loan',
                    ]
                )
            ;
            $builder->get('responsibleForStatus')->addModelTransformer(new EntityToStringTransformer(ProviderFactory::getEntityManager(), ['class' => Person::class, 'multiple' => false]));
        }
        $builder->add('returnAction', ReturnActionType::class, []);
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
        $resolver->setDefaults(
            [
                'translation_domain' => 'Library',
                'data_class' => LibraryItem::class,
            ]
        );
    }
}