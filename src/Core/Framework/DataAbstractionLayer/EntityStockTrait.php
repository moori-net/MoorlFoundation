<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;

trait EntityStockTrait
{
    protected int $stock = 0;
    protected int $availableStock = 0;
    protected int $sales = 0;
    protected ?string $deliveryTimeId = null;
    protected ?DeliveryTimeEntity $deliveryTime = null;
    protected string $productId;
    protected string $productVersionId;
    protected ?ProductDefinition $product = null;

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    /**
     * @return int
     */
    public function getAvailableStock(): int
    {
        return $this->availableStock;
    }

    /**
     * @param int $availableStock
     */
    public function setAvailableStock(int $availableStock): void
    {
        $this->availableStock = $availableStock;
    }

    /**
     * @return int
     */
    public function getSales(): int
    {
        return $this->sales;
    }

    /**
     * @param int $sales
     */
    public function setSales(int $sales): void
    {
        $this->sales = $sales;
    }

    /**
     * @return string|null
     */
    public function getDeliveryTimeId(): ?string
    {
        return $this->deliveryTimeId;
    }

    /**
     * @param string|null $deliveryTimeId
     */
    public function setDeliveryTimeId(?string $deliveryTimeId): void
    {
        $this->deliveryTimeId = $deliveryTimeId;
    }

    /**
     * @return DeliveryTimeEntity|null
     */
    public function getDeliveryTime(): ?DeliveryTimeEntity
    {
        return $this->deliveryTime;
    }

    /**
     * @param DeliveryTimeEntity|null $deliveryTime
     */
    public function setDeliveryTime(?DeliveryTimeEntity $deliveryTime): void
    {
        $this->deliveryTime = $deliveryTime;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return string
     */
    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    /**
     * @param string $productVersionId
     */
    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    /**
     * @return ProductDefinition|null
     */
    public function getProduct(): ?ProductDefinition
    {
        return $this->product;
    }

    /**
     * @param ProductDefinition|null $product
     */
    public function setProduct(?ProductDefinition $product): void
    {
        $this->product = $product;
    }
}
