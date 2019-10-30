<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/10/2019
 * Time: 18:14
 */
namespace Kookaburra\Library\DependencyInjection;

use Kookaburra\Library\Manager\LibraryManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Class KookaburraLibraryExtension
 * @package Kookaburra\Departments\DependencyInjection
 */
class KookaburraLibraryExtension extends Extension
{
    /**
     * load
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $locator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader  = new YamlFileLoader(
            $container,
            $locator
        );
        $loader->load('services.yaml');

        if ($container->has(LibraryManager::class))
        {
            $container
                ->getDefinition(LibraryManager::class)
                ->addMethodCall('setGenerateIdentifier', [$config['generate_identifier']])
                ->addMethodCall('setItemTypes', [$this->loadItemTypes(null)])
                ->addMethodCall('setAllowedBorrowers', [$this->loadAllowedBorrowers(isset($config['allowed_borrowers']) ? $config['allowed_borrowers'] : [])])
                ->addMethodCall('mergeItemTypes', [$this->loadItemTypes(isset($config['item_types']) && is_array($config['item_types']) ? $config['item_types'] : [])])
                ->addMethodCall('setMaximumCopies', [$config['maximum_copies']])
                ->addMethodCall('setBorrowPeriod', [$config['borrow_period']])
                ->addMethodCall('setReservePeriod', [$config['reserve_period']])
                ->addMethodCall('setRenewalMaximum', [$config['renewal_maximum']])
                ->addMethodCall('setLibraryAdministrator', [$config['library_administrator'], $config['library_administrator_email']])
            ;
        }
    }

    /**
     * loadItemTypes
     */
    private function loadItemTypes(?array $itemTypes = null): array
    {
        if (null === $itemTypes)
            $itemTypes = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/item_types.yaml'));

        foreach($itemTypes as $types)
        {
            $resolver = new OptionsResolver();
            $resolver->setRequired(['name','fields','active']);
            $resolver->setAllowedTypes('active', 'boolean');
            $resolver->setAllowedTypes('name', 'string');
            $resolver->setAllowedTypes('fields', 'array');
            $types = $resolver->resolve($types);

            foreach($types['fields'] as $field) {
                $resolver = new OptionsResolver();
                $resolver->setRequired(['name','description','type','options','default','required']);
                $resolver->setAllowedTypes('type', 'string');
                $resolver->setAllowedValues('type', ['Text','Select','Textarea','URL','Date']);
                $resolver->setAllowedTypes('name', 'string');
                $resolver->setAllowedTypes('default', 'string');
                $resolver->setAllowedTypes('description', 'string');
                $resolver->setAllowedTypes('required', 'boolean');
                $field = $resolver->resolve($field);
            }

        }

        return $itemTypes;
    }

    /**
     * loadAllowedBorrowers
     * @param array $allowedBorrowers
     * @return array
     */
    private function loadAllowedBorrowers(array $allowedBorrowers = []){
        if ([] === $allowedBorrowers)
            return ['Students', 'Staff'];

        $x = [];
        foreach($allowedBorrowers as $w)
            if (in_array($w, ['Staff', 'Students', 'Parents', 'Others']))
                $x[] = $w;

        if ([] === $x)
            return ['Students', 'Staff'];

        return $x;
    }
}