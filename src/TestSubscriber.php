<?php declare(strict_types=1);

namespace Shyim\Hooks;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use Shyim\Hooks\Event\BeforeHook;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestSubscriber implements HookSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware\Storefront\Controller\NavigationController::home::before' => 'onBeforeHomeController',
            'Shopware\Storefront\Page\Navigation\NavigationPageLoader::loadMetaData::before' => 'onLoadMetaData',
            'Shopware\Core\Content\Product\SalesChannel\Suggest\ProductSuggestGateway::suggest::before' => 'onProductSearch'
        ];
    }

    public function onBeforeHomeController(BeforeHook $event): void
    {
        $event->setReturn(new JsonResponse('Overwritten home controller. LUL!'));
    }

    public function onLoadMetaData(BeforeHook $event): void
    {
        $event->setReturn(true);

        /** @var NavigationPage $navigationPage */
        $navigationPage = $event->getArguments()[1];
        $navigationPage->getMetaInformation()->setMetaTitle('LUL');
    }

    public function onProductSearch(BeforeHook $event): void
    {
        $product = new ProductEntity();
        $product->setId(Uuid::randomHex());
        $product->setTranslated(['name' => 'Overwritten Suggest *_*']);

        $productCollection = new ProductCollection([$product]);

        $searchResult = new EntitySearchResult(1, $productCollection, null, new Criteria(), Context::createDefaultContext());
        $result = ProductListingResult::createFrom($searchResult);

        $event->setReturn($result);
    }
}
