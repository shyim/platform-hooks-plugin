<?php declare(strict_types=1);

namespace Shyim\Hooks;

use Shyim\Hooks\Container\HookProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SymfonyHookBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new HookProcessor());
        parent::build($container);
    }

    public function boot(): void
    {
        $hookFile = $this->container->getParameter('kernel.cache_dir') . '/hook.php';

        if (!file_exists($hookFile)) {
            return;
        }
        require_once $hookFile;
    }
}
