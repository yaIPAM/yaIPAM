<?php
/**
 * config.dist.php
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 11:53
 */

return [
    "dbase" =>   [
        "dbname"  =>  'yaipam_test',
        "user"  =>  'root',
        "password"  =>  '',
        "host"  =>  '127.0.0.1',
        "driver"    =>  'pdo_mysql'
    ],
    "vlan"  => [
        "maxID" =>  4096,
    ],
    "general"   =>  [
        "site_title"    =>  'yaIPAM - Test',
        "devMode"   =>  false,
        "sitebase"  =>  "yaIPAM",
    ]
];
