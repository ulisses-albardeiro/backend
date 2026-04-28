<?php

namespace App\Entity\Product;

use App\Entity\Company;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductUnit;
use App\Repository\Product\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Brand $brand = null;

    #[ORM\ManyToOne]
    private ?Supplier $supplier = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true, unique: true)]
    private ?string $sku = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $barcode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $purchasePrice = null;

    #[ORM\Column]
    private ?int $salePrice = null;

    #[ORM\Column(enumType: ProductUnit::class)]
    private ?ProductUnit $unit = null;

    #[ORM\Column]
    private ?float $stockQuantity = null;

    #[ORM\Column]
    private ?float $minStock = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $ncm = null;

    #[ORM\Column(enumType: ProductStatus::class)]
    private ?ProductStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productImages;

    /**
     * @var Collection<int, InventoryMovement>
     */
    #[ORM\OneToMany(targetEntity: InventoryMovement::class, mappedBy: 'product', orphanRemoval: true, cascade: ['remove'])]
    private Collection $inventoryMovements;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
        }
    }

    public function __construct()
    {
        $this->productImages = new ArrayCollection();
        $this->inventoryMovements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

    public function setCategory(?ProductCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): static
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPurchasePrice(): ?int
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(int $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function setSalePrice(int $salePrice): static
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    public function getUnit(): ?ProductUnit
    {
        return $this->unit;
    }

    public function setUnit(ProductUnit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getStockQuantity(): ?float
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(float $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function getMinStock(): ?float
    {
        return $this->minStock;
    }

    public function setMinStock(float $minStock): static
    {
        $this->minStock = $minStock;

        return $this;
    }

    public function getNcm(): ?string
    {
        return $this->ncm;
    }

    public function setNcm(?string $ncm): static
    {
        $this->ncm = $ncm;

        return $this;
    }

    public function getStatus(): ?ProductStatus
    {
        return $this->status;
    }

    public function setStatus(ProductStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InventoryMovement>
     */
    public function getInventoryMovements(): Collection
    {
        return $this->inventoryMovements;
    }

    public function addInventoryMovement(InventoryMovement $inventoryMovement): static
    {
        if (!$this->inventoryMovements->contains($inventoryMovement)) {
            $this->inventoryMovements->add($inventoryMovement);
            $inventoryMovement->setProduct($this);
        }

        return $this;
    }

    public function removeInventoryMovement(InventoryMovement $inventoryMovement): static
    {
        if ($this->inventoryMovements->removeElement($inventoryMovement)) {
            // set the owning side to null (unless already changed)
            if ($inventoryMovement->getProduct() === $this) {
                $inventoryMovement->setProduct(null);
            }
        }

        return $this;
    }
}
