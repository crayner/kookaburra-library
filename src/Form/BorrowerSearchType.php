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
 * Date: 7/10/2019
 * Time: 14:29
 */

namespace Kookaburra\Library\Form;

use App\Form\Type\HiddenEntityType;
use Kookaburra\Library\Entity\BorrowerSearch;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Manager\LibraryHelper;
use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BorrowerSearchType
 * @package Kookaburra\Library\Form
 */
class BorrowerSearchType extends AbstractType
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
            ->add('library', HiddenEntityType::class,
                [
                    'class' => Library::class,
              //      'data' => $options['data']->getLibrary() instanceof Library ? $options['data']->getLibrary()->getId() : LibraryHelper::getCurrentLibrary()->getId(),
                ]
            )->add('person', ChoiceType::class,
                [
                    'label' => 'Individual',
                    'choices' => $this->libraryManager->getBorrowerList(),
                    'placeholder' => 'Please select...',
                    'choice_translation_domain' => false,
                    'attr' => [
                        'onChange' => 'this.form.submit()'
                    ],
                ]
            )->add('clear', SubmitType::class,
                [
                    'label' => '<span class="fas fa-broom fa-fw"></span>',
                    'attr' => [
                        'style' => 'float: right;',
                        'title' => 'Clear Search',
                        'class' => 'btn-gibbon',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => BorrowerSearch::class,
                'translation_domain' => 'Library',
            ]
        );
    }
}