<?php declare(strict_types=1);

namespace Shyim\Hooks;

if (file_exists($path = dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once $path;
}

use Shopware\Core\Framework\Plugin;
use Shyim\Hooks\Container\ContainerProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

global $kernel;
$hookFile = dirname($kernel->getKernel()->getCacheDir()) . '/hook.php';
if (file_exists($hookFile)) {
    require_once $hookFile;
}

class Hooks extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ContainerProcessor());
        parent::build($container);
    }
}
