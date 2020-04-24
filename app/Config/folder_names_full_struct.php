<?php
/**
 * Created by PhpStorm.
 * User: BBTO
 * Date: 31/08/16
 * Time: 14:22
 */
$config ['Folders'] ['buoy_system'] = [ 
        'allow_assets' => false, // No se debe permitir agregar folder de assets
//         'nodes' => [ 
//                 'buoy' => 'Buoy',
//                 'ei_control' => 'E&I Control',
//                 'plem' => 'Plem',
//                 'anchoring' => 'Anchoring',
//                 'hoses' => 'Hoses'
//         ],
        'nodes' => [
                'general' => 'General'
        ]
];

// Buoy folders
$config ['Folders'] ['buoy'] = [ 
        'allow_assets' => false, // No se debe permitir agregar folder de assets
        'color' => '#00A5DC',
//         'nodes' => [ 
//                 'structure' => 'Structure',
//                 'product_transfer' => 'Product Transfer',
//                 'mooring' => 'Mooring',
//                 'equipment' => 'Equipment'
//         ]
        'nodes' => []
];

// Buoy folders 2do nivel
$config ['Folders'] ['structure'] = [ 
        'allow_assets' => true, // No se debe permitir agregar folder de assets
        'nodes' => [ 
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

// Buoy folders 2do nivel
$config ['Folders'] ['product_transfer'] = [ 
        'allow_assets' => true, // No se debe permitir agregar folder de assets
        'nodes' => [ 
                'valves' => 'Valves',
                'expansion_piece' => 'Expansion Piece',
                'piping' => 'Piping',
                'swivel' => [ 
                        'Swivel',
                        true
                ]
        ]
];

// Buoy folders 2do nivel
$config ['Folders'] ['mooring'] = [ 
        'allow_assets' => true, // No se debe permitir agregar folder de assets
        'nodes' => [ 
                'chainstoppers' => 'Chainstoppers',
                'mrb' => 'MRB',
                'mrb_greasing' => 'MRB Greasing System',
                'mooring_bridle' => 'Mooring Bridle',
                'mrb_protection' => 'MRB Protection',
                'mooring_hawsers' => 'Mooring Hawsers',
                'tanker_mooring' => 'Tanker Mooring Eqpt'
        ]
];

// Buoy folders 2do nivel
$config ['Folders'] ['equipment'] = [ 
        'allow_assets' => false,
        'nodes' => [ 
                'hoisting_lifting' => 'Hoisting / Lifting',
                'access_tools' => 'Access & Tools',
                'safety' => 'Safety'
        ]
];

// Buoy folders 3er nivel
$config ['Folders'] ['swivel'] = [ 
        'allow_assets' => true,
        'nodes' => [ 
                'cpu_bodies' => 'CPU Bodies',
                'bearing' => 'Bearing',
                'seal' => 'Seal',
                'bolting' => 'Bolting'
        ]
];

// Buoy folders 3er nivel
$config ['Folders'] ['hoisting_lifting'] = [ 
        'allow_assets' => true,
        'nodes' => [ 
                'hoisting_accesories' => 'Hoisting Accesories',
                'winch' => 'Winch'
        ]
];
// Buoy folders 3er nivel
$config ['Folders'] ['access_tools'] = [ 
        'allow_assets' => true,
        'nodes' => [ 
                'railing' => 'Railing',
                'manhole_covers' => 'Manhole Covers',
                'ladders' => 'Ladders',
                'center_well' => 'Center Well'
        ]
];
// Buoy folders 3er nivel
$config ['Folders'] ['safety'] = [ 
        'allow_assets' => true,
        'nodes' => [ 
                'life_buoy' => 'Life Buoy',
                'extinguisher' => 'Extinguisher'
        ]
];

// E&I Control folders
$config ['Folders'] ['ei_control'] = [ 
        'allow_assets' => true,
        'color' => '#2DB928',
        'nodes' => [ 
                'telemetry' => 'Telemetry',
                'navaids' => 'Navaids',
                'hpu' => 'HPU',
                'umbilical' => 'Umbilical'
        ]
];

// PLEM folders
$config ['Folders'] ['plem'] = [ 
        'allow_assets' => true,
        'color' => '#FF2D37',
        'nodes' => [ 
                'subsea_valve' => 'Subsea Valve',
                'plem_piping' => 'Plem Piping',
                'plem_structure' => 'Plem Structure'
        ]
];

// Anchoring folders
$config ['Folders'] ['anchoring'] = [ 
        'allow_assets' => true,
        'color' => '#969B96',
        'nodes' => [ 
                'anchoring_line' => 'Anchoring Line',
                'anchoring_shackles' => 'Anchoring Shackless',
                'anchors' => 'Anchors'
        ]
];

// Hoses folders
$config ['Folders'] ['hoses'] = [ 
        'allow_assets' => false,
        'color' => '#057855',
        'nodes' => [ 
                'subsea' => 'Subsea',
                'floating' => 'Floating'
        ]
];

// Hoses folders 2do nivel
$config ['Folders'] ['subsea'] = [ 
        'allow_assets' => true,
        'nodes' => [ 
                'hose' => 'Hose',
                'body_float' => 'Body float'
        ]
];

// Hoses folders 2do nivel
$config ['Folders'] ['floating'] = [ 
        'allow_assets' => true,
        'nodes' => [ 
                'accessories' => 'Accessories',
                'hose' => 'Hose'
        ]
];

$config ['Folders'] ['sheet_folder'] = [ 
        'allow_assets' => false, // NO se debe permitir agregar folder de assets
//         'nodes' => [ // nodos obligatorios
//                 'general' => 'General',
//                 'historical' => 'Historical',
//                 'manufacturing' => 'Manufacturing',
//                 'contact' => 'Contact'
//         ]
        'nodes' => []
];

/**
 * **************
 * ASSETS FOLDERS
 */
// Assets folders folders
$config ['Folders'] ['assets_folder'] ['structure'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
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
$config ['Folders'] ['assets_folder'] ['product_transfer'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
                'valves' => 'Valves',
                'expansion_piece' => 'Expansion Piece',
                'piping' => 'Piping'
        ]
];
$config ['Folders'] ['assets_folder'] ['swivel'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
                'cpu_bodies' => 'CPU Bodies',
                'bearing' => 'Bearing',
                'seal' => 'Seal',
                'bolting' => 'Bolting'
        ]
];
$config ['Folders'] ['assets_folder'] ['mooring'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
                'chainstoppers' => 'Chainstoppers',
                'mrb' => 'MRB',
                'mrb_greasing' => 'MRB Greasing System',
                'mooring_bridle' => 'Mooring Bridle',
                'mrb_protection' => 'MRB Protection',
                'mooring_hawsers' => 'Mooring Hawsers',
                'tanker_mooring' => 'Tanker Mooring Eqpt'
        ]
];
$config ['Folders'] ['assets_folder'] ['access_tools'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
                'railing' => 'Railing',
                'manhole_covers' => 'Manhole Covers',
                'ladders' => 'Ladders',
                'center_well' => 'Center Well'
        ]
];
$config ['Folders'] ['assets_folder'] ['hoisting_lifting'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
                'hoisting_accesories' => 'Hoisting Accessories',
                'winch' => 'Winch'
        ]
];
$config ['Folders'] ['assets_folder'] ['safety'] = [ 
        'parent' => 'buoy',
        'nodes' => [ // nodos obligatorios
                'life_buoy' => 'Life Buoy',
                'extinguisher' => 'Extinguisher'
        ]
];
$config ['Folders'] ['assets_folder'] ['ei_control'] = [ 
        'parent' => 'ei_control',
        'nodes' => [ // nodos obligatorios
                'telemetry' => 'Telemetry',
                'navaids' => 'Navaids',
                'hpu' => 'HPU',
                'umbilical' => 'Umbilical'
        ]
];
$config ['Folders'] ['assets_folder'] ['plem'] = [ 
        'parent' => 'plem',
        'nodes' => [ // nodos obligatorios
                'subsea_valve' => 'Subsea Valve',
                'plem_piping' => 'Plem Piping',
                'plem_structure' => 'Plem Structure'
        ]
];
$config ['Folders'] ['assets_folder'] ['anchoring'] = [ 
        'parent' => 'anchoring',
        'nodes' => [ // nodos obligatorios
                'anchoring_line' => 'Anchoring Line',
                'anchoring_shackles' => 'Anchoring Shackless',
                'anchors' => 'Anchors'
        ]
];
$config ['Folders'] ['assets_folder'] ['subsea'] = [ 
        'parent' => 'hoses',
        'nodes' => [ // nodos obligatorios
                'hose' => 'Hose',
                'body_float' => 'Body float'
        ]
];
$config ['Folders'] ['assets_folder'] ['floating'] = [ 
        'parent' => 'hoses',
        'nodes' => [ // nodos obligatorios
                'accessories' => 'Accessories',
                'hose' => 'Hose'
        ]
];