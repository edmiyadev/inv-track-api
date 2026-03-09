<?php

namespace App\Enums;

enum PurchaseStatusEnum: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Canceled = 'canceled';
}
