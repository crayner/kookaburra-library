<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 18/11/2019
 * Time: 10:14
 */

namespace Kookaburra\Library\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ToggleType;
use App\Util\TranslationsHelper;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\BorrowerIdentifierList;
use Kookaburra\Library\Entity\Library;
use Kookaburra\RollGroups\Entity\RollGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BorrowerIdentifierListType
 * @package Kookaburra\Library\Form
 */
class BorrowerIdentifierListType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('borrowerHeader', HeaderType::class,
                [
                    'label' => 'Borrower List Generator',
                ]
            )
            ->add('borrowerType', EnumType::class,
                [
                    'label' => 'Borrower List Generator',
                    'choice_list_method' => 'getBorrowerTypes',
                    'choice_list_class' => Library::class,
                    'choice_list_prefix' => 'borrower.type',
                    'placeholder' => 'Please select...',
                    'attr' => [
                        'onChange' => 'toggleRollGroupList()',
                    ]
                ]
            )
            ->add('rollGroup', EntityType::class,
                [
                    'label' => 'Roll Group',
                    'help' => 'Only required with a Student Types',
                    'placeholder' => 'Please select...',
                    'class' => RollGroup::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('rg')
                            ->orderBy('rg.name', 'ASC')
                        ;
                    },
                    'required' => false,
                    'row_id' => 'roll_group_list',
                    'attr' => [
                        'onchange' => 'this.form.submit()',
                    ],
                ]
            )
            ->add('withPhoto', ToggleType::class,
                [
                    'label' => 'Include Borrower Photo',
                    'wrapper_class' => 'flex-1 relative text-right',
                    'submit_on_change' => true,
                ]
            )
            ->add('search', SubmitType::class,
                [
                    'label' => '<span class="fas fa-search fa-fw"></span>',
                    'attr' => [
                        'title' => TranslationsHelper::translate('Search', [], 'messages'),
                    ],
                ]
            )
            ->add('print', SubmitType::class,
                [
                    'label' => '<span class="fas fa-print fa-fw"></span>',
                    'attr' => [
                        'help' => '',
                        'title' => TranslationsHelper::translate('Print', [], 'messages'),
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
        $resolver->setDefaults([
            'translation_domain' => 'Library',
            'data_class' => BorrowerIdentifierList::class,
            'attr' => [
                'id' => 'borrower_identifier_list',
            ],
        ]);
    }
}