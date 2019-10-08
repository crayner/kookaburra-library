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

use App\Form\Type\HeaderType;
use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;

class LibraryItemSubscriber implements EventSubscriberInterface
{
    /**
     * @var LibraryManager
     */
    private $libraryManager;

    /**
     * LibraryItemSubscriber constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }

    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'buildForm',
        ];
    }

    /**
     * buildForm
     * @param PreSubmitEvent $event
     */
    public function buildForm(PreSubmitEvent $event)
    {
        $data = $event->getData();
        if ($data['library'] > 0 && $data['libraryType'] > 0) {
            $form = $event->getForm();

            $form->add('general', HeaderType::class,
                [
                    'label' => 'General Details',
                    'panel' => 'General',
                ]
            )->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Volumne or Product name',
                    'panel' => 'General',
                ]
            );


        }
    }
}