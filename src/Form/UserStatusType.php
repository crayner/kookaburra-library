<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 13/11/2019
 * Time: 07:38
 */

namespace Kookaburra\Library\Form;

use App\Form\Type\ToggleType;
use Kookaburra\Library\Entity\IgnoreStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserStatusType
 * @package Kookaburra\Library\Form
 */
class UserStatusType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('status', ToggleType::class,
            [
                'label' => 'Ignore Status',
                'help' => 'Include all users, regardless of status and current enrolment.',
                'wrapper_class' => 'flex-1 relative text-right',
                'submit_on_change' => true,
            ]
        );
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver-> setDefaults(
            [
                'translation_domain' => 'Library',
                'data_class' => IgnoreStatus::class,
                'attr' => [
                    'id' => 'user_status'
                ],
            ]
        );
    }
}