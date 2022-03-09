<?php
declare(strict_types=1);

namespace LogicSpot\InvoicePdfUpdate\Plugin\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\LayoutInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InvoicePdf to apply after plugin for getPdf method
 */
class InvoicePdf
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * PdfInvoice constructor
     *
     * @param LoggerInterface $logger
     * @param Filesystem $fileSystem
     * @param LayoutInterface $layout
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $fileSystem,
        LayoutInterface $layout
    ) {
        $this->_logger = $logger;
        $this->_fileSystem = $fileSystem;
        $this->_rootDirectory = $fileSystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_layout = $layout;
    }

    /**
     * After plugin to add pdf footer
     *
     * @param Invoice $subject
     * @param \Zend_Pdf $result
     * @return \Zend_Pdf
     */
    public function afterGetPdf(Invoice $subject, \Zend_Pdf $result)
    {
        $page = end($result->pages);
        try {
            $this->drawFooter($subject, $page);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $result;
    }

    /**
     * Draw Pdf Footer
     *
     * @param Invoice $invoice
     * @param \Zend_Pdf_Page $page
     */
    public function drawFooter(Invoice $invoice, \Zend_Pdf_Page $page)
    {
        try {
            $invoice->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->drawLine(25, $invoice->y - 20, 570, $invoice->y - 20);
            $page->drawText(
                strip_tags(trim($this->getFooterContent())),
                240,
                $invoice->y - 40,
                'UTF-8'
            );
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $invoice->y -= 20;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Get Pdf Footer Cms Block Content
     *
     * @return string
     */
    public function getFooterContent()
    {
        return strip_tags(
            $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId('invoice_pdf_footer')->toHtml()
        );
    }
}
