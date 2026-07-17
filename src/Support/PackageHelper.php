<?php

namespace Weboldalnet\CommerceCore\Support;

class PackageHelper
{
    const PACKAGE_NAME = 'Commerce Core modul';
    const PACKAGE_PREFIX = 'commerce-core';

    const PACKAGE_LIST = [
        'database' => [
            'name' => 'database | database/migrations',
            'source' => __DIR__.'/../../database/migrations',
            'destination' => '/database/migrations',
        ],
    ];
}
