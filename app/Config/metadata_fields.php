<?php

$config['Metadata']['fields_hide'] = [
    'valves' => ['size_len','design_pressure','design_temp','number_paths','type','mbl','swl'],
    'bearing' => ['material','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'bolting' => ['design_pressure','design_temp','flange_class','number_paths','type','mbl','swl'],
    'seal' => ['weight','size_len','design_pressure','design_temp','flange_class','number_paths','type','mbl','swl'],
    'chainstoppers' => ['size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'mooring_bridle' => ['size_diam','size_len','design_pressure','design_temp','flange_class','number_paths','type','mbl','swl'],
    'mrb_protection' => ['size_diam','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'mrb_greasing' => ['material','size_diam','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'mooring_hawsers' => ['weight','material','design_pressure','design_temp','flange_class','number_paths','swl'],
    'hoisting_accesories' => ['material','size_diam','size_len','design_pressure','design_temp','flange_class','number_paths','mbl'],
    'telemetry' => ['weight','material','size_diam','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'navaids' => ['weight','material','size_diam','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'hpu' => ['weight','material','reference','size_diam','size_len','design_temp','flange_class','number_paths','mbl','swl'],
    'umbilical' => ['weight','material','size_diam','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'anchors' => ['material','size_diam','design_pressure','design_temp','flange_class','number_paths'],
    'hose' => ['material','design_pressure','design_temp','number_paths','mbl','swl'],
    'body_float' => ['weight','material','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
    'accessories' => ['weight','material','size_len','design_pressure','design_temp','flange_class','number_paths','mbl','swl'],
];
$config['Metadata']['fields'] = [
    'full_assets' => [
        'unique_id_tag' => ['label' => __('Unique ID Tag', true), 'position'=> 0],
        'manufacturer' => ['label' => __('Manufacturer / Origin', true), 'position'=> 1],
        'weight' => ['label' => __('Weight', true), 'position'=> 2],
        'material' => ['label' => __('Material', true), 'position'=> 3],
        'reference' => ['label' => __('Reference', true), 'position'=> 4],
        'size_diam' => ['label' => __('Size (Diameter)', true), 'position'=> 5],
        'size_len' => ['label' => __('Size (Length)', true), 'position'=> 6],
        'design_code' => ['label' => __('Design Code', true), 'position'=> 7],
        'design_pressure' => ['label' => __('Design Pressure', true), 'position'=> 8],
        'design_temp' => ['label' => __('Design Temperature', true), 'position'=> 9],
        'flange_class' => ['label' => __('Flange Class', true), 'position'=> 10],
        'number_paths' => ['label' => __('Number of Paths', true), 'position'=> 11],
        'type' => ['label' => __('Type', true), 'position'=> 12],
        'mbl' => ['label' => __('MBL', true), 'position'=> 13],
        'swl' => ['label' => __('SWL', true), 'position'=> 14],
        'description' => ['label' => __('Description', true), 'position'=> 15],
        'service' => ['label' => __('Service', true), 'position'=> 16],
        'comments' => ['label' => __('Comments', true), 'position'=> 17],
        'delivery_date' => ['label' => __('Delivery date', true), 'position'=> 18],
        'end_life' => ['label' => __('End of life', true), 'position'=> 19],
        'status' => ['label' => __('Status', true), 'position'=> 20],
    ],
    'asset' => [
        'unique_id_tag',
        'manufacturer',
        'weight',
        'material',
        'reference',
        'size_diam',
        'size_len',
        'design_code',
        'design_pressure',
        'design_temp',
        'flange_class',
        'number_paths',
        'type',
        'mbl',
        'swl',
        'description',
        'service',
        'comments',
        'delivery_date',
        'end_life',
        'status',
    ],
    'bs' => [
        'project_number',
        'engineering_name',
        'sb_relative_numbers',
        'client_name',
        'country_code',
        'field_name',
        'longitude',
        'originator',
        'system_function',
        'mooring_system',
        'related_nb',
        'tanker_dwt',
        'product_type',
        'product_throughput',
        'anchor_type',
        'design_load',
        'certifying_authority',
        'present_status',
        'present_location',
        'present_owner',
        'general_comments',
        'original_system',
        'project_scope',
        'year_contract',
        'latitude',
        'number_products',
        'anchor_weight',
        'model_tests',
        'revision_date',
        'water_depth',
        'return_period',
        'directional_conds',
        'survival_hs',
        'operating_hs',
        'period_type',
        'survival_period',
        'operating_period',
        'spectrum',
        'gamma_factor',
        'survival_1min',
        'operating_1min',
        'survival_vc',
        'operating_vc',
        'dimensional_case',
        'ice_layer_thickness',
        'tidal_range',
        'tidal_max',
        'expected_occupancy',
        'environment_comments',
    ]
];