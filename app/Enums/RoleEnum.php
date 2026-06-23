<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMINISTRATOR = 'administrator';
    case REGISTRAR = 'registrar';
    case DEAN = 'dean';
    case DEPARTMENT_CHAIR = 'department_chair';
    case FACULTY = 'faculty';
    case STUDENT = 'student';

    public function label(): string
    {
        return match($this) {
            RoleEnum::SUPER_ADMIN => 'Super Administrator',
            RoleEnum::ADMINISTRATOR => 'Administrator',
            RoleEnum::REGISTRAR => 'Registrar',
            RoleEnum::DEAN => 'Dean',
            RoleEnum::DEPARTMENT_CHAIR => 'Department Chair',
            RoleEnum::FACULTY => 'Faculty',
            RoleEnum::STUDENT => 'Student',
        };
    }
}
