<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 6/11/2019
 * Time: 08:28
 */

namespace Kookaburra\Library\Manager\Traits;


use App\Util\TranslationsHelper;
use Kookaburra\Library\Manager\LibraryHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait LibraryControllerTrait
 * @package Kookaburra\Library\Manager\Traits
 */
trait LibraryControllerTrait
{
    /**
     * LibraryControllerTrait constructor.
     */
    public function __construct(LibraryHelper $helper)
    {
    }

    /**
     * render
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     * @return Response
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $library = LibraryHelper::getCurrentLibrary();
        if ($library)
            $this->addFlash('info', TranslationsHelper::translate('The current library is the \'{name}\'', ['{name}' => $library->getName()], 'Library'));
        return parent::render($view, $parameters, $response);
    }
}