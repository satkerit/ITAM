<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Deployed = 'deployed';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
    case Sold = 'sold';
    case Disposed = 'disposed';
}
