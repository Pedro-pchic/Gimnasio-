<?php

namespace App;

enum QualityCertificateStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Expired = 'expired';
}
