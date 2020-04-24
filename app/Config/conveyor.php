<?php
/**
 * Created by PhpStorm.
 * User: BBTO
 * Date: 31/08/16
 * Time: 14:22
 */

$config['Conveyor']['installed_belt'] = [
        'shell' => [
                '' => '',
            //'PIW' => 'PIW',
            'EP' => 'EP',
            'EPP' => 'EPP',
            'EE' => 'EE',
            'PP' => 'PP',
            'DPP' => 'DPP',
            'ST' => 'ST',
            'UNKW' => 'UNKW',
        ],
        'damages' => [
            '' => '',
            '1' => 'Sin danios',
            '2' => 'Desprendimiento de caucho',
            '3' => 'Cantos mordidos',
            '4' => 'Corte longitudinal',
            '5' => 'Corte transversal',
            '6' => 'Desgaste de cubierta (regular)',
            '7' => 'Marca abrasion extrema',
            '8' => 'Cubiertas desquebrajadas',
            '9' => 'Piola/cable roto',
            '10' => 'Piquete',
            '11' => 'Piquetes multiples'
        ],
        'splice_types' => [
            '' => '',
            '1' => 'Steps',
            '2' => 'Finger',
            '3' => 'Lasin'
        ],
        'splice_quantity' => [
            '' => '',
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => '21',
            '22' => '22',
            '23' => '23',
            '24' => '24',
            '25' => '25',
            '26' => '26',
            '27' => '27',
            '28' => '28',
            '29' => '29',
            '30' => '30',
            '31' => '31',
            '32' => '32',
            '33' => '33',
            '34' => '34',
            '35' => '35',
            '36' => '36',
            '37' => '37',
            '38' => '38',
            '39' => '39',
            '40' => '40',
        ],
        'splice_condition' => [
            '' => '',
            '1' => 'Buena',
            '2' => 'Regular',
            '3' => 'Mala'
        ],
        'history' => [
            '' => '',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10'
        ],
        'last_replacement' => [
            '' => '',
            '1' => 'Buena',
            '2' => 'Regular',
            '3' => 'Mala'
        ],
    'failure_modes' => [
        //'' => '',
        '1' => 'N/A',
        '2' => 'Abrasion or aging/cracking/swelling',
        '3' => 'Belt punctures/tears',
        '4' => 'Cut-Gouge cover damage'
    ]
];

$config['Conveyor']['conveyor'] = [
        'pipe_belt' => [
                '' => '',
                'yes' => 'Si',
                'no' => 'No'
        ],
        'belt_turnover' => [
                '1' => ['Sin guia','img/dropdowns/img_turnover_guided.png'],
                '2' => ['Guiado','img/dropdowns/img_turnover_supported.png'],
                '3' => ['Soportado','img/dropdowns/img_turnover_unguided.png']
        ],
        'direction_turnover' => [
                '' => '',
                '1' => 'Cabeza',
                '2' => 'Cola'
        ],
        'ambient_conditions' => [
                '' => '',
                '1' => 'Constante',
                '2' => 'Alterno'
        ],
        'humidity' => [
                 '' => '',
                'dry' => 'Seco',
                'wet' => 'Mojado'
        ],
        'belt_monitoring_system' => [
                //'' => '',
                '1' => 'No existe',
                '2' => 'Medidor de espesor',
                '3' => 'Elongacion de empalme',
                '4' => 'Inspeccion superficie de banda',
                '5' => 'Escaneo de cable',
                '6' => 'Detector de corte'
        ],
        'housing' => [
                 '' => '',
                '1' => 'No',
                '2' => 'Removible',
                '3' => 'Fijo'
        ],
        'curves' => [
                  '' => '',
                '1' => 'No',
                '2' => 'Vertical',
                '3' => 'Horizontal'
        ],
        'friction_factor' => [
                 '' => '',
                '200' => 'Conveyor with permanent or other well aligned structures with normal maintenance = 0.022',
                '150' => 'Temporary, portable, or poorly aligned conveyors. Also for cold weather conveyors less than zero continuously = 0.030'
        ]
];

$config['Conveyor']['takeups'] = [
        'type' => [
            '127' => ['Screw','img/dropdowns/img_type_screw.png'],
            '128' => ['Automatic','img/dropdowns/img_type_gravity.png'],
            '292' => ['Adapted','img/dropdowns/img_type_adapted.png']
        ]
];

$config['Conveyor']['idlers'] = [
        'part_troughing' => [
                 '' => '',
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '5' => '5'
        ],
        'general_condition' => [
                 '' => '',
                '1' => 'Buena',
                '2' => 'Pobre',
                '3' => 'Moderada'
        ],
        'stuck_idlers' => [
            '' => '',
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => 'Otro'
        ],
        'offset_idlers' => [
                '' => '',
                '1' => 'In-line',
                '2' => 'Offset equal roll',
                '3' => 'Offset lc roll',
                '4' => 'Otro'
        ],
        'misalignment_sensor_upper' => [
            '' => '',
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => 'Otro'
        ],
    'misalignment_sensor_lower' => [
        '' => '',
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9',
        '10' => '10',
        '11' => '11',
        '12' => '12',
        '13' => '13',
        '14' => '14',
        '15' => '15',
        '16' => '16',
        '17' => '17',
        '18' => '18',
        '19' => '19',
        '20' => '20',
        '21' => 'Otro'
    ]
];

$config['Conveyor']['pulleys'] = [
        'motriz_lagging' => [
                '' => '',
                '1' => 'No',
                '2' => 'Liso',
                '3' => 'Recubierto hule',
                '4' => 'Ceramica'
        ],
        'surface_condition' => [
                  '' => '',
                '1' => 'Buena',
                '2' => 'Regular',
                '3' => 'Mala'
        ],
        'brake_device' => [
                 '' => '',
                'yes' => 'Si',
                'no' => 'No'
        ],
        'lagging_type' => [
                 '' => '',
                '1' => 'No',
                '2' => 'Diamante',
                '3' => 'Boton',
                '4' => 'Ceramico',
                '5' => 'Cola pescado'
        ]
];

$config['Conveyor']['transition'] = [
        'pressure_outer_idlers' => [
            '' => '',
             'yes' => 'Si',
             'no' => 'No'
        ],
        'material_guidance' => [
            '' => '',
            'yes' => 'Si',
            'no' => 'No'
        ]
];

$config['Conveyor']['remarks'] = [
        'maintenance_condition' => [
            '' => '',
            '1' => 'Buena',
            '3' => 'Moderada',
            '2' => 'Pobre',
        ],
        'overall_status' => [
            '' => '',
            '1' => 'Verde',
            '2' => 'Amarillo',
            '3' => 'Rojo'
        ]
];