<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\SalesAttribute\Plugin\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use OnTap\SalesAttribute\Api\Data\QuoteItemExtraInterface;
use OnTap\SalesAttribute\Model\ResourceModel\QuoteItemExtra;
use Magento\Framework\Api\ExtensionAttributesFactory;
use OnTap\SalesAttribute\Model\QuoteItemExtraFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class CartRepositoryPlugin
{
    /**
     * @var QuoteItemExtra
     */
    protected QuoteItemExtra $quoteItemExtraResource;

    /**
     * @var ExtensionAttributesFactory
     */
    protected ExtensionAttributesFactory $extensionAttributesFactory;

    /**
     * @var QuoteItemExtraFactory
     */
    protected QuoteItemExtraFactory $quoteItemExtraFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var Grouped
     */
    protected Grouped $groupedType;

    /**
     * CartItemRepositoryPlugin constructor.
     * @param QuoteItemExtra $quoteItemExtraResource
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param QuoteItemExtraFactory $quoteItemExtraFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Grouped $groupedType
     */
    public function __construct(
        QuoteItemExtra $quoteItemExtraResource,
        ExtensionAttributesFactory $extensionAttributesFactory,
        QuoteItemExtraFactory $quoteItemExtraFactory,
        ProductRepositoryInterface $productRepository,
        Grouped $groupedType
    ) {
        $this->quoteItemExtraResource = $quoteItemExtraResource;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->quoteItemExtraFactory = $quoteItemExtraFactory;
        $this->productRepository = $productRepository;
        $this->groupedType = $groupedType;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param $result
     * @param CartInterface $quote
     */
    public function afterSave(
        CartRepositoryInterface $subject,
        $result,
        CartInterface $quote
    ) {
        /** @var CartItemInterface $item */
        foreach ($quote->getAllItems() as $item) {
            $extensionAttributes = $item->getExtensionAttributes();
            if ($extensionAttributes && !$extensionAttributes->getQuoteItemExtra()) {
                /** @var QuoteItemExtraInterface $quoteItemExtra */
                $quoteItemExtra = $this->quoteItemExtraFactory
                    ->create()
                    ->load($item->getItemId());

                $product = $item->getProduct();
                $parentIds = $this->groupedType->getParentIdsByChild($product->getId());
                if (!isset($parentIds[0])) {
                    continue;
                }

                $groupedProduct = $this->productRepository->getById($parentIds[0]);

                $quoteItemExtra
                    ->setItemId($item->getItemId())
                    ->setBusinessId(
                        $groupedProduct->getData('business_id')
                    );

                $this->quoteItemExtraResource->save($quoteItemExtra);

                $quoteItemExtension = $this->extensionAttributesFactory
                    ->create(CartItemInterface::class)
                    ->setQuoteItemExtra($quoteItemExtra);

                $item->setExtensionAttributes($quoteItemExtension);
            }
        }
    }
}
