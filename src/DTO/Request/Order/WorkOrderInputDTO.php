<?php

namespace App\DTO\Request\Order;

use App\Enum\Order\WorkOrderStatus;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class WorkOrderInputDTO
{
    #[Assert\NotBlank(message: "O título é obrigatório")]
    #[Assert\Length(max: 255)]
    public string $title;

    public ?string $code;

    #[Assert\NotBlank(message: "O cliente é obrigatório")]
    #[Assert\Positive]
    public int $customerId;

    #[Assert\NotBlank(message: "A empresa é obrigatória")]
    #[Assert\Positive]
    public int $companyId;

    public ?int $quoteId = null;

    #[Assert\NotBlank(message: "O status é obrigatório")]
    public WorkOrderStatus $status;

    #[Assert\NotBlank(message: "A descrição do problema é obrigatória")]
    public string $problemDescription;

    public ?string $technicalReport = null;

    public ?string $equipment = null;

    public ?DateTimeImmutable $startDate = null;

    public ?DateTimeImmutable $endDate = null;

    /** 
     * @var WorkOrderItemInputDTO[] 
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: "A OS deve ter pelo menos um item")]
    public array $items = [];
}
