<?php

namespace App;

enum CommercialPartnerType: string
{
    case Product = 'product';
    case Service = 'service';
    case Mixed = 'mixed';
}
