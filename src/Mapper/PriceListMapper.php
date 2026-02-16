<?php

namespace App\Mapper;

use App\Enum\UnitType;
use App\Entity\Company;
use App\Entity\PriceList;
use App\Entity\PriceListItem;
use App\DTO\Request\PriceListInputDTO;
use App\DTO\Response\PriceListOutputDTO;
use App\DTO\Response\PriceListItemOutputDTO;

class PriceListMapper
{
    public function toEntity(PriceListInputDTO $dto, Company $company, ?PriceList $priceList = null): PriceList
    {
        $priceList = $priceList ?? new PriceList();

        $priceList->setCompany($company);
        $priceList->setTitle($dto->title);
        $priceList->setDescription($dto->description);

        if ($priceList->getId()) {
            foreach ($priceList->getPriceListItems() as $oldItem) {
                $priceList->removePriceListItem($oldItem);
            }
        }

        foreach ($dto->items as $itemDto) {
            /** @var PriceListItemInputDTO $itemDto */
            $item = new PriceListItem();
            $item->setName($itemDto->name);
            $item->setQuantity($itemDto->quantity);
            $item->setUnit($itemDto->unit);

            $priceList->addPriceListItem($item);
        }

        return $priceList;
    }

    public function toOutputDTO(PriceList $priceList): PriceListOutputDTO
    {
        $items = array_map(function (PriceListItem $item) {
            return new PriceListItemOutputDTO(
                id: $item->getId(),
                name: $item->getName(),
                quantity: $item->getQuantity(),
                unit: $item->getUnit()->value,
                unitLabel: $item->getUnit()->getLabel()
            );
        }, $priceList->getPriceListItems()->toArray());

        return new PriceListOutputDTO(
            id: $priceList->getId(),
            companyId: $priceList->getCompany()->getId(),
            title: $priceList->getTitle(),
            description: $priceList->getDescription(),
            createdAt: $priceList->getCreatedAt(),
            updatedAt: $priceList->getUpdatedAt(),
            items: $items
        );
    }
}
