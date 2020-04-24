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
                'plem' => 'Plem',
                'anchoring' => 'Anchor',
                'hoses' => 'Hoses',
        ]
];

//Buoy folders
$config['Folders']['buoy'] = [
        'allow_assets' => false, //No se debe permitir agregar folder de assets
        'color' => '#00A5DC',
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
        'allow_assets' => false, //se debe permitir agregar folder de assets
        'nodes' => [ //nodos obligatorios
                'hoisting_lifting' => 'Hoisting / Lifting',
                'access_tools' => 'Access & Tools',
                'safety' => 'Safety'
        ],
];

//Ei_control folders
$config['Folders']['ei_control'] = [
        'allow_assets' => true, //se debe permitir agregar folder de assets
        'color' => '#2DB928',
        'nodes' => []
];

//Anchoring folders
$config['Folders']['plem'] = [
        'allow_assets' => true, //se debe permitir agregar folder de assets
        'color' => '#FF2D37',
        'nodes' => []
];

//Plem folders
$config['Folders']['anchoring'] = [
        'allow_assets' => true, //se debe permitir agregar folder de assets
        'color' => '#969B96',
        'nodes' => []
];

//Hoses folders
$config['Folders']['hoses'] = [
        'allow_assets' => false, //se debe permitir agregar folder de assets
        'color' => '#057855',
        'nodes' => [ //nodos obligatorios
                'subsea' => 'Subsea',
                'floating' => 'Floating' 
        ]
];


//Folders GHMC
$config['Folders']['sheet_folder'] = [
        'allow_assets' => false, //NO se debe permitir agregar folder de assets
        'nodes' => [ //nodos obligatorios
                'general' => 'General',
                'historical' => 'Historical',
                'manufacturing' => 'Manufacturing',
                'contact' => 'Contact', 
        ]
];

/****************
 * ASSETS FOLDERS
 */
//Assets folders folders
$config['Folders']['assets_folder']['structure'] = [
        'nodes' => [ //nodos obligatorios
                'hull' => 'Hull',
                'turntable' => 'Turntable',
                'chainhawse' => 'Chainhawse Support',
                'launch_platform' => 'Launch Platform',
                'bollard' => 'Bollard',
                'mooring_platform' => 'Mooring Platform',
                'cathodic_protection' => 'Cathodic Protection',
                'piping_platform' => 'Piping Platform'
        ]
];
$config['Folders']['assets_folder']['product_transfer'] = [
        'nodes' => [ //nodos obligatorios
                'valves' => 'Valves',
                'expansion_piece' => 'Expansion Piece',
                'piping' => 'Piping',
        ]
];
$config['Folders']['assets_folder']['swivel'] = [
        'nodes' => [ //nodos obligatorios
                'cpu_bodies' => 'CPU Bodies',
                'bearing' => 'Bearing',
                'seal' => 'Seal',
                'bolting' => 'Bolting'
        ]
];
$config['Folders']['assets_folder']['mooring'] = [
        'nodes' => [ //nodos obligatorios
                'chainstoppers' => 'Chainstoppers',
                'mrb' => 'MRB',
                'mrb_greasing' => 'MRB Greasing System',
                'mooring_bridle' => 'Mooring Bridle',
                'mrb_protection' => 'MRB Protection',
                'mooring_hawsers' => 'Mooring Hawsers',
                'tanker_mooring' => 'Tanker Mooring Eqpt'
        ]
];
$config['Folders']['assets_folder']['access_tools'] = [
        'nodes' => [ //nodos obligatorios
                'railing' => 'Railing',
                'manhole_covers' => 'Manhole Covers',
                'ladders' => 'Ladders',
                'center_well' => 'Center Well'
        ]
];
$config['Folders']['assets_folder']['hoisting_lifting'] = [
        'nodes' => [ //nodos obligatorios
                'hoisting_accesories' => 'Hoisting Accesories',
                'winch' => 'Winch'
        ]
];
$config['Folders']['assets_folder']['safety'] = [
        'nodes' => [ //nodos obligatorios
                'life_buoy' => 'Life Buoy',
                'extinguisher' => 'Extinguisher'
        ]
];
$config['Folders']['assets_folder']['ei_control'] = [
        'nodes' => [ //nodos obligatorios
                'telemetry' => 'Telemetry',
                'navaids' => 'Navaids',
                'hpu' => 'HPU',
                'umbilical' => 'Umbilical'
        ]
];
$config['Folders']['assets_folder']['plem'] = [
        'nodes' => [ //nodos obligatorios
                'subsea_valve' => 'Subsea Valve',
                'plem_piping' => 'Plem Piping',
                'plem_structure' => 'Plem Structure'
        ]
];
$config['Folders']['assets_folder']['anchoring'] = [
        'nodes' => [ //nodos obligatorios
                'anchoring_line' => 'Anchoring Line',
                'anchoring_shackles' => 'Anchoring Shackless',
                'anchors' => 'Anchors'
        ]
];
$config['Folders']['assets_folder']['subsea'] = [
        'nodes' => [ //nodos obligatorios
                'hose' => 'Hose',
                'body_float' => 'Body float'
        ]
];
$config['Folders']['assets_folder']['floating'] = [
        'nodes' => [ //nodos obligatorios
                'accessories' => 'Accessories',
                'hose' => 'Hose'
        ]
];
