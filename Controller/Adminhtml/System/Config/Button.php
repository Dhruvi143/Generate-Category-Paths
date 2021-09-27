<?php

namespace Codilar\CategoryForGTM\Controller\Adminhtml\System\Config;

use Codilar\CategoryForGTM\Model\Config\Configuration;
use Codilar\CategoryForGTM\Model\CronJob;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Button
 * @package Codilar\CategoryForGTM\Controller\Adminhtml\System\Config
 */
class Button extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CronJob
     */
    private $cronJob;
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param CronJob $cronJob
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CronJob $cronJob,
        Configuration $configuration

    )
    {
        parent::__construct($context);
        $this->logger = $logger;
        $this->cronJob = $cronJob;
        $this->configuration = $configuration;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            if($this->configuration->getLoggerStatus())
            $this->logger->info('Initiated');
            $this->cronJob->generateCategoryPaths();
            if($this->configuration->getLoggerStatus())
            $this->logger->info('Completed');
        }
        catch (\Exception $exception)
        {
            $this->logger->info('Error in Controller/Button:'.$exception->getMessage());
        }
    }
}

