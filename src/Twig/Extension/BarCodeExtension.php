<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 18/11/2019
 * Time: 11:34
 */

namespace Kookaburra\Library\Twig\Extension;

use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class BarCodeExtension
 * @package Kookaburra\Library\Twig\Extension
 */
class BarCodeExtension extends AbstractExtension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'barcode_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('generateBarcode', [$this, 'create']),
        ];
    }

    /**
     * create
     * @param string $data
     * @param array $options
     * @return string
     */
    public function create(string $data, array $options = []): string
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults(
            [
                'code' => 'C39E+',
                'width' => -4,
                'height' => -4,
                'colour' => 'black',
                'bgColour' => 'white',
                'padding' => [-2,-2,-2,-2],
                'style' => 'png',
            ]
        );

        $options = $resolver->resolve($options);

        $barcode = new Barcode();

        try {
            $bobj = $barcode->getBarcodeObj(
                $options['code'],                          // barcode type and additional comma-separated parameters
                $data,                                      // data string to encode
                $options['width'],                          // bar width (use absolute or negative value as multiplication factor)
                $options['height'],                         // bar height (use absolute or negative value as multiplication factor)
                $options['colour'],                         // foreground color
                $options['padding'],                        // padding (use absolute or negative values as multiplication factors)
            )->setBackgroundColor($options['bgColour']);    // background color
        } catch (Exception $e) {
            return 'Error';
        } catch (\Com\Tecnick\Color\Exception $e) {
            return 'Error';
        }

        // output the barcode as HTML div (see other output formats in the documentation and examples)
        switch ($options['style']) {
             case 'html':
                 return $bobj->getHtmlDiv();
        }
        return base64_encode($bobj->getPngData());
    }
}