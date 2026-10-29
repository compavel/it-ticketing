<?php

namespace App;

enum UserRole: string
{
    case User = 'user';
    case Technician = 'technician';
    case Admin = 'admin';
}
