<?php declare(strict_types=1);

namespace Shyim\Hooks\Container;


use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Shyim\Hooks\Event\BeforeHook;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HookBuilder
{
    private $cacheDir;

    public function __construct(string $cacheDir)
    {
        $cacheDir .= '/hooks/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir);
        }

        $this->cacheDir = $cacheDir;
    }

    public function build(array $classes): bool
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $nodeFinder = new NodeFinder();
        $builder = new BuilderFactory();
        $printer = new Standard();

        $initFile = dirname($this->cacheDir, 2) . '/hook.php';
        file_put_contents($initFile, '<?php' . PHP_EOL);
        $didChangedSomething = false;

        foreach ($classes as $className => $filePath) {
            $list = explode('\\', $className);
            $lastPart = array_pop($list);

            $path = $this->cacheDir . implode(DIRECTORY_SEPARATOR, $list);
            $hookCachePath = $path . '/' . $lastPart . '.php';

            // We have already a hook
            if (file_exists($hookCachePath)) {
                continue;
            }

            $stmts = $parser->parse(file_get_contents($filePath));

            /** @var Class_ $class */
            $class = $nodeFinder->findFirstInstanceOf($stmts, Class_::class);
            array_unshift($class->stmts, $builder->property('shyimEventDispatcher')->getNode());

            $propertyFetch = $builder->propertyFetch($builder->var('this'), 'shyimEventDispatcher');

            $methods = $nodeFinder->findInstanceOf($stmts, ClassMethod::class);
            $didAddedConstructor = false;

            /** @var ClassMethod $method */
            foreach ($methods as $method) {
                $name = (string) $method->name;

                if ($name === '__construct' && !$didAddedConstructor) {
                    $param = $builder->param('shyimEventDispatcher')->getNode();
                    $param->type = new Identifier('\\' . EventDispatcherInterface::class);
                    $method->params[] = $param;
                    $method->stmts[] = new Expression(new Assign($propertyFetch, $builder->var('shyimEventDispatcher')));
                    $didAddedConstructor = true;
                } else {
                    $arg = $builder->new('\\' . BeforeHook::class, [$builder->funcCall('func_get_args')]);
                    $eventName = $className . '::' . (string) $method->name . '::before';

                    $event = $builder->var('shyimEventDispatcherEvent');
                    $newStmt = new Expression(new Assign($event, $builder->methodCall($propertyFetch, 'dispatch', [$arg, new String_($eventName)])));

                    $ret = new Return_($builder->methodCall($event, 'getReturn'));

                    if ($method->returnType instanceof Identifier && $method->returnType->name === 'void') {
                        $ret = new Return_();
                    }

                    $condition = new If_($builder->methodCall($event, 'hasReturn'));
                    $condition->stmts = [$ret];

                    array_unshift($method->stmts, $condition);
                    array_unshift($method->stmts, $newStmt);
                }
            }

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($hookCachePath, $printer->prettyPrintFile($stmts));
            file_put_contents($initFile, sprintf('require_once "%s";', $hookCachePath), FILE_APPEND);
            $didChangedSomething = true;
        }

        return $didChangedSomething;
    }
}
