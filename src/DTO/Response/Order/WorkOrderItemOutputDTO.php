<?php

namespace App\DTO\Response\Order;

readonly class WorkOrderItemOutputDTO
{
    public function __construct(
        public int $id,
        public string $description,
        public string $quantity,
        public int $unitPrice,
        public int $totalPrice,
        public ?int $productId,
        public ?string $productName,
        public ?int $laborId,
        public ?string $laborName,
    ) {}
}
