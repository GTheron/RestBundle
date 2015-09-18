<?php

namespace GTheron\RestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GTheronRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //$container->setParameter('g_theron_rest.use_security', $config['use_security']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->loadSecurity($config, $loader, $container);
    }

    private function loadSecurity(array $config, Loader\YamlFileLoader $loader, ContainerBuilder $container)
    {
        if(true === $config['use_security'])
        {
            $loader->load('security.yml');

            //TODO store actual service name in variable
            $container->setAlias('g_theron_rest.resource_manager', 'g_theron_rest.security.resource_manager');
            $container->setAlias('g_theron_rest.authorization_manager', 'g_theron_rest.security.authorization_manager');
        }
    }
}
