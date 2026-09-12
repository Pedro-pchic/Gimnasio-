<?php

namespace App;

enum SaleDetailType: string
{
    case Membership = 'membership';
    case Service = 'service';
    case Other = 'other';
}
