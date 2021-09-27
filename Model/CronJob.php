<?php
namespace Codilar\CategoryForGTM\Model;

use Codilar\CategoryForGTM\Model\Config\Configuration;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CronJob
 * @package Codilar\CategoryForGtm\Model
 */
class CronJob
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var Collection
     */
    private $collection;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Action
     */
    private $productAction;
    /**
     * @var Configuration
     */
    private $configuration;


    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     * @param CategoryRepository $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Collection $collection
     * @param StoreManagerInterface $storeManager
     * @param Action $productAction
     */
    public function __construct(
        ProductRepository       $productRepository,
        LoggerInterface         $logger,
        CategoryRepository      $categoryRepository,
        SearchCriteriaBuilder   $searchCriteriaBuilder,
        Collection              $collection,
        StoreManagerInterface   $storeManager,
        Action                  $productAction,
        Configuration           $configuration
    )
    {

        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->collection = $collection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        $this->configuration = $configuration;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateCategoryPaths()
    {
        $pathArray = [];
        $data = [];
        $productIds = [];
        $storeId = $this->storeManager->getStore()->getId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('type_id','configurable','eq')
            ->addFilter('category_path',true,'null')
            ->create();
        $productList = $this->productRepository->getList($searchCriteria);
        $productItems = $productList->getItems();
        foreach ($productItems as $productItem)
        {
            $productIds[] = $productItem->getId();
            if($this->configuration->getLoggerStatus())
            $this->logger->info('Parent Product Id:'.$productItem->getId());
            $productTypeInstance = $productItem->getTypeInstance();
            $usedProducts = $productTypeInstance->getUsedProducts($productItem);
            foreach ($usedProducts as $childProduct)
            {
                $categoryIds = $childProduct->getCategoryIds();
                foreach ($categoryIds as $categoryId)
                {
                    $categoryInstance = $this->categoryRepository->get($categoryId);
                    $level = $categoryInstance->getLevel();
                    if($level == 5)
                    {
                        $categories = $this->collection->addAttributeToSelect('name')->getItems();
                        foreach ($categories as $category)
                        {
                            $path = array_slice(explode('/',$category->getPath()),2);
                            foreach ($path as $key=>$value)
                            {
                                $path[$key] = str_replace('/','\/',$categories[$value]->getName());
                            }
                            $pathArray[$category->getId()] = strtolower(join('/',$path));
                        }
                        $result = $pathArray[$categoryId];
                        if($productItem->getCustomAttribute('static_product_name')) {
                            $value = $productItem->getCustomAttribute('static_product_name')->getValue();
                            $fullCategoryPath = $result.'/'.$value;
                            if($this->configuration->getLoggerStatus())
                            $this->logger->info('Category Path:'.$fullCategoryPath);
                            $data = [
                                'category_path'=>$fullCategoryPath
                            ];
                        }
                        else{
                            if($this->configuration->getLoggerStatus())
                            $this->logger->info('Static Product Name is not Set');
                        }

                        break;
                    }
                }
                break;
            }
            $this->productAction->updateAttributes($productIds,$data,$storeId);
            if($this->configuration->getLoggerStatus())
            $this->logger->info('saved');
        }
    }
}

