<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Product\ProductEntity;

trait EntityStockTrait
{
    protected int $stock = 0;
    protected int $availableStock = 0;
    protected int $sales = 0;
    protected string $productId;
    protected string $productVersionId;
    protected ?ProductEntity $product = null;

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
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     */
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }
}
