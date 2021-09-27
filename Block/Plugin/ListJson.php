<?php
namespace Codilar\CategoryForGTM\Block\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Class ListJson
 * @package Codilar\CategoryForGtm\Block\Plugin
 */
class ListJson
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Http
     */
    private $request;

    /**
     * @param LoggerInterface $logger
     * @param Registry $registry
     * @param Http $request
     */
    public function __construct(
        LoggerInterface             $logger,
        Registry                    $registry,
        Http                        $request,
    )
    {
        $this->logger = $logger;
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * @param \Magento\GoogleTagManager\Block\ListJson $subject
     * @param $category
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetCurrentCategoryName(\Magento\GoogleTagManager\Block\ListJson $subject, $result)
    {
        $pageType = $this->request->getFullActionName();
        if ($pageType == 'catalog_product_view') {
            $product = $this->registry->registry('current_product');
            $productType = $product->getTypeId();
            if($productType == 'configurable') {
                $result = $product->getCategoryPath();
                $this->logger->info($result);
                $this->logger->info('Hitting');
            }
            else{
                $this->logger->info('Product type is not configurable');
            }
        }
        return $result;
    }
}
