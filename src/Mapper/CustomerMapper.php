<?php

namespace App\Mapper;

use App\Entity\Company;
use App\Entity\Customer;
use App\DTO\Request\CustomerInputDTO;
use App\DTO\Response\CustomerOutputDTO;

class CustomerMapper
{
    public function toEntity(CustomerInputDTO $dto, Company $company, ?Customer $customer = null): Customer
    {
        $customer ??= new Customer();

        $customer->setCompany($company);
        $customer->setType($dto->type);
        $customer->setName($dto->name);
        $customer->setTradingName($dto->tradingName);
        $customer->setDocument($dto->document);
        $customer->setStateRegistration($dto->stateRegistration);
        $customer->setEmail($dto->email);
        $customer->setPhone($dto->phone);
        $customer->setZipCode($dto->zipCode);
        $customer->setStreet($dto->street);
        $customer->setNumber($dto->number);
        $customer->setComplement($dto->complement);
        $customer->setNeighborhood($dto->neighborhood);
        $customer->setCity($dto->city);
        $customer->setState($dto->state);
        $customer->setStatus($dto->status);
        $customer->setNotes($dto->notes);

        return $customer;
    }

    public function toOutputDTO(Customer $customer): CustomerOutputDTO
    {
        return new CustomerOutputDTO(
            id: $customer->getId(),
            type: $customer->getType()->value,
            typeLabel: $customer->getType()->getLabel(),
            name: $customer->getName(),
            tradingName: $customer->getTradingName(),
            document: $customer->getDocument(),
            stateRegistration: $customer->getStateRegistration(),
            email: $customer->getEmail(),
            phone: $customer->getPhone(),
            zipCode: $customer->getZipCode(),
            street: $customer->getStreet(),
            number: $customer->getNumber(),
            complement: $customer->getComplement(),
            neighborhood: $customer->getNeighborhood(),
            city: $customer->getCity(),
            state: $customer->getState(),
            status: $customer->isStatus(),
            notes: $customer->getNotes(),
            createdAt: $customer->getCreatedAt(),
            updatedAt: $customer->getUpdatedAt(),
        );
    }
}
