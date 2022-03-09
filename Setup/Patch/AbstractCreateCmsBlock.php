<?php
declare(strict_types=1);

namespace LogicSpot\InvoicePdfUpdate\Setup\Patch;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractCreateCmsBlock
 * Abstract class for data patches that create new CMS block.
 */
abstract class AbstractCreateCmsBlock
{
    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param BlockFactory $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        BlockFactory $blockFactory,
        BlockRepositoryInterface $blockRepository,
        LoggerInterface $logger
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @param string $identifier
     * @param string $title
     * @param array|null $storeIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createCmsBlock(string $identifier, string $title, array $storeIds = null)
    {
        if (!$storeIds) {
            $storeIds = [Store::DEFAULT_STORE_ID];
        }

        $content = $this->getBlockContent($identifier);
        if (!$content) {
            $this->logger->error(sprintf(
                'Missing content for CMS block "%s"',
                $identifier
            ));

            return;
        }

        $cmsBlockData = [
            BlockInterface::TITLE => $title,
            BlockInterface::IDENTIFIER => $identifier,
            BlockInterface::CONTENT => $content,
            BlockInterface::IS_ACTIVE => 1,
            'stores' => $storeIds
        ];

        $cmsBlock = $this->blockFactory->create()
            ->load($identifier, BlockInterface::IDENTIFIER);

        if (!$cmsBlock->getId()) {
            $cmsBlock->setData($cmsBlockData);
        } else {
            $cmsBlock->setContent($cmsBlockData[BlockInterface::CONTENT]);
        }

        $this->blockRepository->save($cmsBlock);
    }

    /**
     * Delete the CMS block
     *
     * @param string $identifier
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function deleteCmsBlock(string $identifier)
    {
        try {
            $this->blockRepository->deleteById($identifier);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }

    /**
     * Return the content of HTML file with the CMS block's content
     *
     * @param string $identifier
     * @return false|string
     */
    protected function getBlockContent(string $identifier)
    {
        $path = $this->getContentDir()
            . $identifier
            . '.html';

        return file_get_contents($path);
    }

    /**
     * Return the directory path to block's content
     *
     * @return string
     */
    protected function getContentDir(): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                __DIR__,
                '..',
                'cms',
                'block'
            ]
        ) . DIRECTORY_SEPARATOR;
    }
}
