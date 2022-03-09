<?php
declare(strict_types=1);

namespace LogicSpot\InvoicePdfUpdate\Setup\Patch\Data;

use LogicSpot\InvoicePdfUpdate\Setup\Patch\AbstractCreateCmsBlock;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Class CreateCmsFooterBlockWithAbout
 * Create CMS block for Invoice Pdf Footer.
 */
class InstallCmsBlockInvoicePdfFooter extends AbstractCreateCmsBlock implements DataPatchInterface, PatchRevertableInterface
{
    const BLOCK_IDENTIFIER = 'invoice_pdf_footer';

    /**
     * @return $this|InstallCmsBlockInvoicePdfFooter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->createCmsBlock(
            self::BLOCK_IDENTIFIER,
            'Invoice Pdf Footer'
        );

        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revert()
    {
        $this->deleteCmsBlock(self::BLOCK_IDENTIFIER);
    }
}
