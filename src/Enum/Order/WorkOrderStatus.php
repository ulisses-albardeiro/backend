<?php

namespace App\Enum\Order;

enum WorkOrderStatus: string
{
    case PENDING = 'pending';
    case DRAFT = 'draft';
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case WAITING_PARTES = 'waiting_parts'

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pendente',
            self::DRAFT => 'Rascunho',
            self::OPEN => 'Aberta',
            self::IN_PROGRESS => 'Em progresso',
            self::COMPLETED => 'Completada',
            self::CANCELED => 'Cancelado',
            self::WAITING_PARTES => 'Aguardando Peças'
        };
    }
}
