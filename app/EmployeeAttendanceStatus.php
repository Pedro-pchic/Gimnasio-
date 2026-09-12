<?php

namespace App;

enum EmployeeAttendanceStatus: string
{
    case Present = 'present';
    case Finalized = 'finalized';
    case Absence = 'absence';
    case Justified = 'justified';
}
