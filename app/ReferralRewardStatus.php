<?php

namespace App;

enum ReferralRewardStatus: string
{
    case Pending = 'pending';
    case Available = 'available';
    case Used = 'used';
    case Cancelled = 'cancelled';
}
