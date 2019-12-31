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
 * Date: 14/11/2019
 * Time: 16:39
 */

namespace Kookaburra\Library\Form;

use Kookaburra\Library\Entity\LibraryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RapidLoanItemType
 * @package Kookaburra\Library\Form
 */
class RapidLoanItemType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      //  $item = $options['item'];
        $builder->add('id', HiddenType::class)
            ->add('name', HiddenType::class)
            ->add('imageLocation', HiddenType::class)
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
                'translation_domain' => 'Library',
                'data_class' => LibraryItem::class,
            ]
        );
    }
}