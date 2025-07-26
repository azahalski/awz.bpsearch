<?php
return [
    'ui.entity-selector' => [
        'value' => [
            'entities' => [
                [
                    'entityId' => 'awzbpsearch-user',
                    'provider' => [
                        'moduleId' => 'awz.bpsearch',
                        'className' => '\\Awz\\Bpsearch\\Access\\EntitySelectors\\User'
                    ],
                ],
                [
                    'entityId' => 'awzbpsearch-group',
                    'provider' => [
                        'moduleId' => 'awz.bpsearch',
                        'className' => '\\Awz\\Bpsearch\\Access\\EntitySelectors\\Group'
                    ],
                ],
            ]
        ],
        'readonly' => true,
    ]
];