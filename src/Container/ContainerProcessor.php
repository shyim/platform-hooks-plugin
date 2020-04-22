<?php declare(strict_types=1);

namespace Shyim\Hooks\Container;

use Shyim\Hooks\HookSubscriber;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContainerProcessor implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $neededHooksForClasses = [];

        foreach ($container->findTaggedServiceIds('shyim.hook_subscriber') as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $def->getClass();
            if (!isset(class_implements($class)[HookSubscriber::class])) {
                throw new \RuntimeException(sprintf('Subscriber "%s" needs to extend from %s', $id, HookSubscriber::class));
            }

            $def->addTag('kernel.event_subscriber');

            $methods = array_keys($class::getSubscribedEvents());

            foreach ($methods as $method) {
                $class = explode('::', $method)[0];

                if (!class_exists($class)) {
                    throw new \RuntimeException(sprintf('Found invalid "%s" subscribe in subscriber "%s": Class does not exists', $method, $id));
                }

                if (!isset($neededHooksForClasses[$class])) {
                    $neededHooksForClasses[$class] = (new \ReflectionClass($class))->getFileName();
                }
            }
        }

        $needRefresh = (new HookBuilder($container->getParameter('kernel.cache_dir')))->build($neededHooksForClasses);

        foreach ($container->getDefinitions() as $definition) {
            if (isset($neededHooksForClasses[$definition->getClass()])) {
                $definition->addArgument(new Reference('event_dispatcher'));
            }
        }

        if ($needRefresh) {
            if (PHP_SAPI !== 'cli') {
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit();
            }

            echo 'Generated Hooks. To ensure anything works correctly. Please restart the command again!'. PHP_EOL;
            exit();
        }
    }
}
