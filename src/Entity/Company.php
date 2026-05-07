<?php

namespace App\Entity;

use App\Entity\Customer\Customer;
use App\Entity\Labor\Labor;
use App\Entity\Labor\LaborCategory;
use App\Entity\Order\WorkOrder;
use App\Entity\Product\Brand;
use App\Entity\Product\InventoryMovement;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\Supplier;
use App\Entity\Quote\Quote;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $tradingName = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $registrationNumber = null;

    #[ORM\Column(length: 50)]
    private ?string $stateRegistration = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $complement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $neighborhood = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 50)]
    private ?string $state = null;

    #[ORM\OneToOne(inversedBy: 'company', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Customer>
     */
    #[ORM\OneToMany(targetEntity: Customer::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $customers;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $categories;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $transactions;

    /**
     * @var Collection<int, Quote>
     */
    #[ORM\OneToMany(targetEntity: Quote::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $quotes;

    /**
     * @var Collection<int, Receipt>
     */
    #[ORM\OneToMany(targetEntity: Receipt::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $receipts;

    /**
     * @var Collection<int, PriceList>
     */
    #[ORM\OneToMany(targetEntity: PriceList::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $priceLists;

    /**
     * @var Collection<int, ProductCategory>
     */
    #[ORM\OneToMany(targetEntity: ProductCategory::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $productCategories;

    /**
     * @var Collection<int, Brand>
     */
    #[ORM\OneToMany(targetEntity: Brand::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $brands;

    /**
     * @var Collection<int, Supplier>
     */
    #[ORM\OneToMany(targetEntity: Supplier::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $suppliers;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'company')]
    private Collection $products;

    /**
     * @var Collection<int, InventoryMovement>
     */
    #[ORM\OneToMany(targetEntity: InventoryMovement::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $inventoryMovements;

    /**
     * @var Collection<int, LaborCategory>
     */
    #[ORM\OneToMany(targetEntity: LaborCategory::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $laborCategories;

    /**
     * @var Collection<int, Labor>
     */
    #[ORM\OneToMany(targetEntity: Labor::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $labors;

    /**
     * @var Collection<int, WorkOrder>
     */
    #[ORM\OneToMany(targetEntity: WorkOrder::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $workOrders;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->quotes = new ArrayCollection();
        $this->receipts = new ArrayCollection();
        $this->priceLists = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->inventoryMovements = new ArrayCollection();
        $this->laborCategories = new ArrayCollection();
        $this->labors = new ArrayCollection();
        $this->workOrders = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTradingName(): ?string
    {
        return $this->tradingName;
    }

    public function setTradingName(string $tradingName): static
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getStateRegistration(): ?string
    {
        return $this->stateRegistration;
    }

    public function setStateRegistration(string $stateRegistration): static
    {
        $this->stateRegistration = $stateRegistration;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): static
    {
        $this->complement = $complement;

        return $this;
    }

    public function getNeighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function setNeighborhood(?string $neighborhood): static
    {
        $this->neighborhood = $neighborhood;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

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
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setCompany($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getCompany() === $this) {
                $customer->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setCompany($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCompany() === $this) {
                $category->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setCompany($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCompany() === $this) {
                $transaction->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Quote>
     */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(Quote $quote): static
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
            $quote->setCompany($this);
        }

        return $this;
    }

    public function removeQuote(Quote $quote): static
    {
        if ($this->quotes->removeElement($quote)) {
            // set the owning side to null (unless already changed)
            if ($quote->getCompany() === $this) {
                $quote->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Receipt>
     */
    public function getReceipts(): Collection
    {
        return $this->receipts;
    }

    public function addReceipt(Receipt $receipt): static
    {
        if (!$this->receipts->contains($receipt)) {
            $this->receipts->add($receipt);
            $receipt->setCompany($this);
        }

        return $this;
    }

    public function removeReceipt(Receipt $receipt): static
    {
        if ($this->receipts->removeElement($receipt)) {
            // set the owning side to null (unless already changed)
            if ($receipt->getCompany() === $this) {
                $receipt->setCompany(null);
            }
        }

        return $this;
    }

    public function getSubDir(?string $subDir): string
    {
        if ($this->getCreatedAt()) {
            return 'company_' . md5($this->getCreatedAt()->format('U')) . $subDir;
        }

        return '';
    }

    /**
     * @return Collection<int, PriceList>
     */
    public function getPriceLists(): Collection
    {
        return $this->priceLists;
    }

    public function addPriceList(PriceList $priceList): static
    {
        if (!$this->priceLists->contains($priceList)) {
            $this->priceLists->add($priceList);
            $priceList->setCompany($this);
        }

        return $this;
    }

    public function removePriceList(PriceList $priceList): static
    {
        if ($this->priceLists->removeElement($priceList)) {
            // set the owning side to null (unless already changed)
            if ($priceList->getCompany() === $this) {
                $priceList->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): static
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories->add($productCategory);
            $productCategory->setCompany($this);
        }

        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): static
    {
        if ($this->productCategories->removeElement($productCategory)) {
            // set the owning side to null (unless already changed)
            if ($productCategory->getCompany() === $this) {
                $productCategory->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Brand>
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(Brand $brand): static
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
            $brand->setCompany($this);
        }

        return $this;
    }

    public function removeBrand(Brand $brand): static
    {
        if ($this->brands->removeElement($brand)) {
            // set the owning side to null (unless already changed)
            if ($brand->getCompany() === $this) {
                $brand->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Supplier>
     */
    public function getSuppliers(): Collection
    {
        return $this->suppliers;
    }

    public function addSupplier(Supplier $supplier): static
    {
        if (!$this->suppliers->contains($supplier)) {
            $this->suppliers->add($supplier);
            $supplier->setCompany($this);
        }

        return $this;
    }

    public function removeSupplier(Supplier $supplier): static
    {
        if ($this->suppliers->removeElement($supplier)) {
            // set the owning side to null (unless already changed)
            if ($supplier->getCompany() === $this) {
                $supplier->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCompany($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCompany() === $this) {
                $product->setCompany(null);
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
            $inventoryMovement->setCompany($this);
        }

        return $this;
    }

    public function removeInventoryMovement(InventoryMovement $inventoryMovement): static
    {
        if ($this->inventoryMovements->removeElement($inventoryMovement)) {
            // set the owning side to null (unless already changed)
            if ($inventoryMovement->getCompany() === $this) {
                $inventoryMovement->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LaborCategory>
     */
    public function getLaborCategories(): Collection
    {
        return $this->laborCategories;
    }

    public function addLaborCategory(LaborCategory $laborCategory): static
    {
        if (!$this->laborCategories->contains($laborCategory)) {
            $this->laborCategories->add($laborCategory);
            $laborCategory->setCompany($this);
        }

        return $this;
    }

    public function removeLaborCategory(LaborCategory $laborCategory): static
    {
        if ($this->laborCategories->removeElement($laborCategory)) {
            // set the owning side to null (unless already changed)
            if ($laborCategory->getCompany() === $this) {
                $laborCategory->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Labor>
     */
    public function getLabors(): Collection
    {
        return $this->labors;
    }

    public function addLabor(Labor $labor): static
    {
        if (!$this->labors->contains($labor)) {
            $this->labors->add($labor);
            $labor->setCompany($this);
        }

        return $this;
    }

    public function removeLabor(Labor $labor): static
    {
        if ($this->labors->removeElement($labor)) {
            // set the owning side to null (unless already changed)
            if ($labor->getCompany() === $this) {
                $labor->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkOrder>
     */
    public function getWorkOrders(): Collection
    {
        return $this->workOrders;
    }

    public function addWorkOrder(WorkOrder $workOrder): static
    {
        if (!$this->workOrders->contains($workOrder)) {
            $this->workOrders->add($workOrder);
            $workOrder->setCompany($this);
        }

        return $this;
    }

    public function removeWorkOrder(WorkOrder $workOrder): static
    {
        if ($this->workOrders->removeElement($workOrder)) {
            // set the owning side to null (unless already changed)
            if ($workOrder->getCompany() === $this) {
                $workOrder->setCompany(null);
            }
        }

        return $this;
    }
}
