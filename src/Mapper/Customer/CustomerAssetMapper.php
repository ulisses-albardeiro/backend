<?php

namespace App\Mapper\Customer;

use App\DTO\Request\Customer\CustomerAssetInputDTO;
use App\Entity\Company;
use App\Entity\Customer\Customer;
use App\DTO\Response\Customer\CustomerAssetOutputDTO;
use App\Entity\Customer\CustomerAsset;

class CustomerAssetMapper
{
    public function toEntity(CustomerAssetInputDTO $dto, Company $company, ?Customer $customer): CustomerAsset
    {
        $customerAsset ??= new CustomerAsset();

        $customerAsset->setCustomer($customer);
        $customerAsset->setName($dto->name);
        $customerAsset->setCompany($company);
    
        return $customerAsset;
    }

    public function toOutputDTO(CustomerAsset $customerAsset): CustomerAssetOutputDTO
    {        
        return new CustomerAssetOutputDTO(
            id: $customerAsset->getId(),
            customerId: $customerAsset->getCustomer()->getId(),
            customerName: $customerAsset->getCustomer()->getName(),
            name: $customerAsset->getName(),
            createdAt: $customerAsset->getCreatedAt()
        );
    }
}
