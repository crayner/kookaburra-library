<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/11/2019
 * Time: 13:34
 */

namespace Kookaburra\Library\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Form\LibraryType;
use Kookaburra\Library\Manager\LibraryHelper;
use Kookaburra\Library\Manager\Traits\LibraryControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SettingController
 * @package Kookaburra\Library\Controller
 * @Route("/library", name="library__")
 */
class SettingController extends AbstractController
{
    use LibraryControllerTrait;

    /**
     * settings
     * @param Library|null $library
     * @Route("/{library}/settings/", name="settings")
     */
    public function settings(Request $request, ContainerManager $manager, LibraryHelper $helper,  int $library = 0)
    {
        $library = ProviderFactory::getRepository(Library::class)->find($library) ?: new Library();
        $manager->setShowSubmitButton(true);
        $container = new Container();
        $container->setTarget('formContent');
        TranslationsHelper::setDomain('Library');

        $form = $this->createForm(LibraryType::class, $library, ['action' => $this->generateUrl('library__settings', ['library' => $library->getId() ?: 0])]);

        if ($request->getContentType() === 'json'){
            $errors = [];
            $status = 'success';
            $content = json_decode($request->getContent(), true);
            if (intval($content['workingOn']) !== intval($library->getId()) && $content['submit_clicked'] === 'workingOn') {
                $library = ProviderFactory::getRepository(Library::class)->find(intval($content['workingOn'])) ?: new Library();
                $form = $this->createForm(LibraryType::class, $library, ['action' => $this->generateUrl('library__settings', ['library' => $library->getId() ?: 0])]);
                $manager->singlePanel($form->createView());
                dump($library);
                if ($library->getId() > 0) {
                    $helper::setCurrentLibrary($library);
                    $this->addFlash('warning', TranslationsHelper::translate('The current library has been switched to \'{name}\'', ['{name}' => $library->getName()], 'Library'));
                    return new JsonResponse(
                        [
                            'form' => $manager->getFormFromContainer('formContent', 'single'),
                            'errors' => $errors,
                            'status' => 'redirect',
                            'redirect' => $this->generateUrl('library__settings', ['library' => $library->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                        ],
                        200);
                }
                return new JsonResponse(
                    [
                        'form' => $manager->getFormFromContainer('formContent', 'single'),
                        'errors' => $errors,
                        'status' => $status,
                    ],
                    200);

            }

            unset($content['submit_clicked']);
            $form->submit($content);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if (is_array($content['bgImage']))
                    $content['bgImage'] = null;
                if (strlen($content['bgImage']) > 1310720) {
                    $status = 'error';
                    $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('return.error.1', [], 'messages')];
                    $form->get('bgImage')->addError(new FormError(TranslationsHelper::translate('The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.', ['{{ size }}' => strlen($content['bgImage']) / 1024, '{{ suffix }}' => 'kB', '{{ limit }}' => 1250])));
                } else {
                    if (strlen($content['bgImage']) > 0)
                        $library->setBgImage(ImageHelper::convertJsonToImage($content['bgImage'], 'library_bg_', 'Library'));
                    $em->persist($library);
                    $em->flush();
                    $errors[] = ['class' => 'success', 'message' => TranslationsHelper::translate('return.success.0', [], 'messages')];
                    $form = $this->createForm(LibraryType::class, $library, ['action' => $this->generateUrl('library__settings', ['library' => $library->getId() ?: 0])]);
                }
            } else {
                $status = 'error';
                $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('return.error.1', [], 'messages')];
            }
            $manager->singlePanel($form->createView());
            return new JsonResponse(
                [
                    'form' => $manager->getFormFromContainer('formContent', 'single'),
                    'errors' => $errors,
                    'status' => $status,
                ],
                200);
        }
        $manager->singlePanel($form->createView());
        return $this->render('@KookaburraLibrary/library_setting.html.twig',
            [
                'library' => $library,
                'form' => $form->createView(),
            ]
        );
    }
}