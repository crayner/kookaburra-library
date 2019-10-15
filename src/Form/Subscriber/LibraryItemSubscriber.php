<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 20:38
 */

namespace Kookaburra\Library\Form\Subscriber;

use App\Entity\Department;
use App\Entity\Space;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryType;
use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LibraryItemSubscriber implements EventSubscriberInterface
{
    /**
     * @var LibraryManager
     */
    private $libraryManager;
    /**
     * @var array
     */
    private $options;

    /**
     * LibraryItemSubscriber constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
        $this->libraryManager->setTranslations();
    }

    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    /**
     * buildForm
     * @param PreSubmitEvent $event
     */
    public function preSubmit(PreSubmitEvent $event)
    {
        $data = $event->getData();
        if ($data['library'] > 0 && $data['libraryType'] > 0) {
            $type = null;
            if ($this->libraryManager->isGenerateIdentifier() && isset($data['identifier']) && '' === $data['identifier']) {
                $item = new LibraryItem();
                $library = ProviderFactory::getRepository(Library::class)->find($data['library']);
                $type = ProviderFactory::getRepository(LibraryType::class)->find($data['libraryType']);
                $item->setLibrary($library)->setLibraryType($type);
                $data['identifier'] = $this->libraryManager->newIdentifier($item)->getIdentifier();
            }
            $form = $event->getForm();
            $this->setData($data);
            $this->buildForm($form);
            $this->buildFields($form);
        }

        $event->setData($data);

    }

    /**
     * getOptions
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Options.
     *
     * @param array $options
     * @return LibraryItemSubscriber
     */
    public function setOptions(array $options): LibraryItemSubscriber
    {
        $this->options = $options;
        return $this;
    }

    /**
     * postSetData
     * @param PostSetDataEvent $event
     */
    public function postSetData(PostSetDataEvent $event)
    {
        if ($event->getData() instanceof LibraryItem && $event->getData()->getId() > 0)
        {
            $form = $event->getForm();
            $this->buildForm($form);
            $this->setData($event->getData());
            $this->buildFields($form,$event->getData());
        }

    }

    /**
     * buildForm
     * @param Form $form
     */
    private function buildForm(Form $form)
    {
        $form
            ->add('general', HeaderType::class,
                [
                    'label' => 'General Details',
                    'help' => 'Item Identifier {identifier}',
                    'help_translation_parameters' => ['{identifier}' => $this->getOptions()['data']->getIdentifier()],
                    'panel' => 'General',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Volume or Product name',
                    'panel' => 'General',
                ]
            )
        ;
        if (!$this->libraryManager->isGenerateIdentifier())
            $form->add('identifier', TextType::class,
                [
                    'label' => 'Identifier',
                    'help' => 'Must be unique',
                    'panel' => 'General',
                ]
            );
        else
            $form->add('identifier', HiddenType::class,
                [
                    'panel' => 'General',
                ]
            );

        $form
            ->add('producer', TextType::class,
                [
                    'label' => 'Author/Brand',
                    'help' => 'Who created the item?',
                    'panel' => 'General',
                ]
            )
            ->add('vendor', TextType::class,
                [
                    'label' => 'Vendor',
                    'help' => 'Who supplied the item?',
                    'panel' => 'General',
                    'required' => false,
                ]
            )
            ->add('purchaseDate', DateType::class,
                [
                    'label' => 'Purchase Date',
                    'panel' => 'General',
                ]
            )
            ->add('invoiceNumber', TextType::class,
                [
                    'label' => 'Invoice Number',
                    'panel' => 'General',
                    'required' => false,
                ]
            )
            ->add('imageType', EnumType::class,
                [
                    'label' => 'Image Type',
                    'panel' => 'General',
                    'placeholder' => '',
                    'choice_list_prefix' => false,
                    'required' => false,
                ]
            )
            ->add('space', EntityType::class,
                [
                    'label' => 'Location',
                    'class' => Space::class,
                    'panel' => 'General',
                    'placeholder' => 'Please Select...',
                    'required' => false,
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.name');
                    },
                ]
            )
            ->add('locationDetail', TextType::class,
                [
                    'label' => 'Location Details',
                    'help' => 'Shelf, cabinet, sector, etc',
                    'panel' => 'General',
                    'required' => false,
                ]
            )
            ->add('ownershipType', EnumType::class,
                [
                    'label' => 'Ownership Type',
                    'panel' => 'General',
                    'choice_list_prefix' => false,
                    'required' => false,
                ]
            )
            ->add('department', EntityType::class,
                [
                    'label' => 'Department',
                    'class' => Department::class,
                    'help' => 'Which department is responsible for the item?',
                    'panel' => 'General',
                    'placeholder' => 'Please select...',
                    'required' => false,
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->orderBy('d.name')
                            ;
                    },
                ]
            )
            ->add('bookable', ToggleType::class,
                [
                    'label' => 'Bookable As Facility?',
                    'help' => 'Can item be booked via Facility Booking in Timetable? Useful for laptop carts, etc.',
                    'panel' => 'General',
                ]
            )
            ->add('borrowable', ToggleType::class,
                [
                    'label' => 'Borrowable?',
                    'help' => 'Is item available for loan?',
                    'panel' => 'General',
                ]
            )
            ->add('status', EnumType::class,
                [
                    'label' => 'Status?',
                    'help' => 'Availability',
                    'choice_list_prefix' => false,
                    'panel' => 'General',
                ]
            )
            ->add('replacement', ToggleType::class,
                [
                    'label' => 'Plan Replacement?',
                    'panel' => 'General',
                ]
            )
            ->add('physicalCondition', EnumType::class,
                [
                    'label' => 'Physical Condition',
                    'choice_list_prefix' => false,
                    'panel' => 'General',
                ]
            )
            ->add('comment', TextareaType::class,
                [
                    'label' => 'Comments/Notes',
                    'attr' => [
                        'rows' => 10,
                        'cols' => 30,
                    ],
                    'panel' => 'General',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit All',
                    'panel' => 'General',
                ]
            )
        ;
    }

    /**
     * buildFields
     * @param Form $form
     */
    private function buildFields(Form $form)
    {

        $fields = [];

        $data = $this->getData();

        $libraryType = ProviderFactory::getRepository(LibraryType::class)->find($data['libraryType']);

        $form
            ->add('specific', HeaderType::class,
                [
                    'label' => 'Specific Details',
                    'help' => 'Item Identifier {identifier}',
                    'help_translation_parameters' => ['{identifier}' => $this->getOptions()['data']->getIdentifier()],
                    'panel' => 'Specific',
                ]
            )
        ;
        if ($libraryType->getName() === 'Print Publication')
            $form
                ->add('googleLoad', ButtonType::class,
                    [
                        'label' => 'Get Book Data From Google',
                        'panel' => 'Specific',
                        'on_click' => 'loadGoogleBookData',
                    ]
                )
            ;

        foreach($libraryType->getFields() as $q=>$field)
        {
            $options = [];
            $options['constraints'] = [];
            $options['label'] = $field['name'];
            if ('' !== $field['description'])
                $options['help'] = $field['description'];
            if (in_array($field['type'], ['Text','URL']) && $field['options'] > 0) {
                $options['constraints'] = array_merge([
                    new Length(['max' => intval($field['options'])])
                ], $options['constraints']);
            }
            $options['required'] = $field['required'] === 'Y' ? true : false;
            if ($options['required'])
                $options['constraints'] = array_merge([new NotBlank()],$options['constraints']);

            $options['data'] = isset($this->getOptions()['data']->getFields()[$q]) ? $this->getOptions()['data']->getFields()[$q] : $field['default'];

            if ('Select' === $field['type']) {
                $choices = [];
                foreach(explode(',',$field['options']) as $choice)
                    $choices[$choice] = $choice;
                $options['choices'] = $choices;
                $options['constraints'] = array_merge([new Choice(['choices' => $options['choices']])], $options['constraints']);
            }

            if ('Textarea' === $field['type']) {
                $options['attr'] = [
                    'rows' => 6,
                    'cols' => 60,
                ];
            }

            $options['mapped'] = false;
            $options['panel'] = 'Specific';
            $options['data'] = isset($data['field'.$q]) ? $data['field'.$q] : '';

            switch ($field['type']) {
                case 'Text':
                    $form->add('field'.$q, TextType::class, $options);
                    break;
                case 'Select':
                    $form->add('field'.$q, ChoiceType::class, $options);
                    break;
                case 'Textarea':
                    $form->add('field'.$q, TextareaType::class, $options);
                    break;
                case 'URL':
                    $form->add('field'.$q, UrlType::class, $options);
                    break;
                default:
                    dump($field['type']);
            }

            if (isset($data['field'.$q])) {
                $fields[$field['name']] = $data['field'.$q];
            }

            $data['fields'] = $fields;

        }

        $form
            ->add('submit2', SubmitType::class,
                [
                    'label' => 'Submit All',
                    'panel' => 'Specific',
                ]
            )
        ;
    }

    private $data;

    /**
     * getData
     * @return LibraryType
     */
    public function getData(): array
    {
        $data = [];
        if ($this->data instanceof LibraryItem) {
            $data['libraryType'] = $this->data->getLibraryType()->getId();
            $fields = $this->data->getFields();
            reset($fields);
            foreach($this->data->getLibraryType()->getFields() as $q=>$field) {
                $w = current($fields);
                $data['field' . $q] = $w;
                next($fields);
            }
            $this->data = $data;
        }
        return $this->data;
    }

    /**
     * Data.
     *
     * @param mixed $data
     * @return LibraryItemSubscriber
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


}