<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
