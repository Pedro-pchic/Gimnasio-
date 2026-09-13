<?php

namespace App;

enum ReferralStatus: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Cancelled = 'cancelled';
}
