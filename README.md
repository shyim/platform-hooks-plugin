# An experiment to implement hooks into Shopware 6

# Installation

* Run `composer install` in the Plugin Folder
* Install the plugin
* When you add new hooks, clear the cache manually `rm -rf var/cache/*`

# Usages

Hooks can be placed on all DI classes and on all methods (yes even private).
The Subscribers needs to be registered in the DI using tag `shyim.hook_subscriber`

## Before-Hooking

Should be used to manipulate arguments or stopping the execution of an method

```php
<?php declare(strict_types=1);

namespace Shyim\Hooks;

use Shyim\Hooks\Event\BeforeHook;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestSubscriber implements HookSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware\Storefront\Controller\NavigationController::home::before' => 'onBeforeHomeController',
        ];
    }

    public function onBeforeHomeController(BeforeHook $event): void
    {
        // Return always this json response. Other execution of code is stopped
        $event->setReturn(new JsonResponse('Overwritten home controller. LUL!'));
       
        // Set some argument
        $event->setArgument(0, 'adsds');
    }
}
```

## After Hooking

This should be used to edit the return of an method

```php
<?php declare(strict_types=1);

namespace Shyim\Hooks;

use Shyim\Hooks\Event\AfterHook;

class TestSubscriber implements HookSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware\Storefront\Controller\NavigationController::home::after' => 'onAfterHomeController',
        ];
    }

    public function onAfterHomeController(AfterHook $event): void
    {
        $event->getReturn()->headers->set('x-header', 'We added a new header to the response');
    }
}
```
