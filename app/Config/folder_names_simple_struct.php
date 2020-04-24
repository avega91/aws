<?php
/**
 * Created by PhpStorm.
 * User: BBTO
 * Date: 31/08/16
 * Time: 14:22
 */

$config['Folders']['buoy_system'] = [
        'allow_assets' => false, //No se debe permitir agregar folder de assets
        'nodes' => [ // nodos obligatorios del folder
                'buoy' => 'Buoy',
                'ei_control' => 'E&I Control',
                'plem' => 'PLEM',
                'anchoring' => 'Anchor',
                'hoses' => 'HOSES',
        ]
];

//Buoy folders
$config['Folders']['buoy'] = [
        'allow_assets' => false, //No se debe permitir agregar folder de assets
        'nodes' => [// nodos obligatorios del folder
                'structure' => 'Structure',
                'product_transfer' => 'Product Transfer',
                'mooring' => 'Mooring',
                'equipment' => 'Equipment',
        ]
];

// Buoy folders 2do nivel
$config['Folders']['product_transfer'] = [
        'allow_assets' => true, //se debe permitir agregar folder de assets
        'nodes' => [ //nodos obligatorios
                'swivel' => 'Swivel',
        ],
];
// Buoy folders 2do nivel
$config['Folders']['equipment'] = [
        'allow_assets' => true, //se debe permitir agregar folder de assets
        'nodes' => [ //nodos obligatorios
                'hoisting_lifting' => 'Hoisting / Lifting',
                'access_tools' => 'Access & Tools',
                'safety' => 'Safety'
        ],
];

//Hoses folders
$config['Folders']['hoses'] = [
        'allow_assets' => true, //se debe permitir agregar folder de assets
        'nodes' => [ //nodos obligatorios
                'subsea' => 'Subsea',
                'floating' => 'Floating' 
        ]
];


//Assets folders folders
$config['Folders']['assets_folder'] = [
        'allow_assets' => false, //NO se debe permitir agregar folder de assets
        'nodes' => [ //nodos obligatorios
                'general' => 'General',
                'historical' => 'Historical',
                'manufacturing' => 'Manufacturing',
                'contact' => 'Contact', 
        ]
];

