<?php

namespace App;

enum SaleDetailType: string
{
    case Membership = 'membership';
    case Service = 'service';
    case Other = 'other';
    case ThirdPartyProduct = 'third_party_product';
    case ThirdPartyService = 'third_party_service';

    /**
     * @return array<int, string>
     */
    public static function thirdPartyValues(): array
    {
        return [self::ThirdPartyProduct->value, self::ThirdPartyService->value];
    }
}
