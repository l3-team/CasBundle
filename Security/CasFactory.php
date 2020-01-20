<?php

namespace L3\Bundle\CasBundle\Security;


use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Reference;
use L3\Bundle\CasBundle\Security\CasProvider;
use L3\Bundle\CasBundle\Security\CasListener;

class CasFactory implements SecurityFactoryInterface {
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint) {
        $providerId = 'security.authentication.provider.cas.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition('cas.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.cas.'.$id;
	$listener = $container->setDefinition($listenerId, new ChildDefinition('cas.security.authentication.listener'));
	
        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * Defines the position at which the provider is called.
     * Possible values: pre_auth, form, http, and remember_me.
     *
     * @return string
     */
    public function getPosition() {
        return 'pre_auth';
    }

    public function getKey() {
        return 'cas';
    }

    public function addConfiguration(NodeDefinition $node) {
    }

} 
