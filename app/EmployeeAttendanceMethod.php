<?php

namespace App;

enum EmployeeAttendanceMethod: string
{
    case Manual = 'manual';
    case Code = 'code';
    case Biometric = 'biometric';
}
