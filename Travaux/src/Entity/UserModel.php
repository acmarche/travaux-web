<?php

namespace AcMarche\Travaux\Entity;

use LdapRecord\Models\Model;

class UserModel extends Model
{
    protected ?string $connection = 'employe';

    public static array $objectClasses = [
        'person',
        'organizationalPerson',
        'user',
        'top',
    ];

    public string $uid;
    public array $attributes = [];



}
