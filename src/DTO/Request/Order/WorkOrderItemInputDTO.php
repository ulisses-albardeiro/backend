<?php

namespace App\DTO\Request\Order;

use Symfony\Component\Validator\Constraints as Assert;

class WorkOrderItemInputDTO
{
    #[Assert\NotBlank(message: "A descrição do item é obrigatória")]
    public string $description;

    #[Assert\NotBlank(message: "A quantidade é obrigatória")]
    #[Assert\Positive]
    public string $quantity;

    #[Assert\NotBlank(message: "O preço unitário é obrigatório")]
    #[Assert\PositiveOrZero]
    public int $unitPrice;

    #[Assert\Positive]
    public ?int $productId = null;

    #[Assert\Positive]
    public ?int $laborId = null;
}
