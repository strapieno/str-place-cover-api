<?php
return [
    'service_manager' => [
        'factories' => [
            'Strapieno\Utils\Listener\ListenerManager' => 'Strapieno\Utils\Listener\ListenerManagerFactory'
        ],
        'invokables' => [
            'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator' => 'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator'
        ],
        'aliases' => [
            'listenerManager' => 'Strapieno\Utils\Listener\ListenerManager'
        ]
    ],
    // Register listener to listener manager
    'service-listeners' => [
        'initializers' => [
            'Strapieno\Place\Model\PlaceModelInitializer'
        ],
        'invokables' => [
            'Strapieno\PlaceCover\Api\Listener\PlaceRestListener'
                => 'Strapieno\PlaceCover\Api\Listener\PlaceRestListener'
        ]
    ],
    'attach-resource-listeners' => [
        'Strapieno\PlaceCover\Api\V1\Rest\Controller' => [
            'Strapieno\PlaceCover\Api\Listener\PlaceRestListener'
        ]
    ],
    'controllers' => [
        'delegators' => [
            'Strapieno\PlaceCover\Api\V1\Rest\Controller' => [
                'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator',
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'api-rest' => [
                'child_routes' => [
                    'place' => [
                        'child_routes' => [
                            'cover' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/cover',
                                    'defaults' => [
                                        'controller' => 'Strapieno\PlaceCover\Api\V1\Rest\Controller'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'imgman-apigility' => [
        'imgman-connected' => [
            'Strapieno\PlaceCover\Api\V1\Rest\ConnectedResource' => [
                'service' => 'ImgMan\Service\PlaceCover'
            ],
        ],
    ],
    'zf-rest' => [
        'Strapieno\PlaceCover\Api\V1\Rest\Controller' => [
            'service_name' => 'place-cover',
            'listener' => 'Strapieno\PlaceCover\Api\V1\Rest\ConnectedResource',
            'route_name' => 'api-rest/place/cover',
            'route_identifier_name' => 'place_id',
            'entity_http_methods' => [
                0 => 'GET',
                2 => 'PUT',
                3 => 'DELETE'
            ],
            'page_size' => 10,
            'page_size_param' => 'page_size',
            'collection_class' => 'Zend\Paginator\Paginator',
            'entity_class' => 'Strapieno\PlaceCover\Model\Entity\CoverEntity'
        ]
    ],
    'zf-content-negotiation' => [
        'accept_whitelist' => [
            'Strapieno\PlaceCover\Api\V1\Rest\Controller' => [
                'application/hal+json',
                'application/json'
            ],
        ],
        'content_type_whitelist' => [
            'Strapieno\PlaceCover\Api\V1\Rest\Controller' => [
                'application/json',
                'multipart/form-data',
            ],
        ],
    ],
    'zf-hal' => [
        // map each class (by name) to their metadata mappings
        'metadata_map' => [
            'Strapieno\PlaceCover\Model\Entity\CoverEntity' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api-rest/place/cover',
                'route_identifier_name' => 'place_id',
                'hydrator' => 'Strapieno\Utils\Hydrator\ImageBase64Hydrator',
            ],
        ],
    ],
    'zf-content-validation' => [
        'Strapieno\PlaceCover\Api\V1\Rest\Controller' => [
            'input_filter' => 'PlaceCoverInputFilter',
        ],
    ],
    'strapieno_input_filter_specs' => [
        'PlaceCoverInputFilter' => [
            [
                'name' => 'blob',
                'required' => true,
                'allow_empty' => false,
                'continue_if_empty' => false,
                'validators' => [
                    0 => [
                        'name' => 'fileuploadfile',
                        'break_chain_on_failure' => true,
                    ],
                    1 => [
                        'name' => 'filesize',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'min' => '20KB',
                            'max' => '8MB',
                        ],
                    ],
                    2 => [
                        'name' => 'filemimetype',
                        'options' => [
                            'mimeType' => [
                                'image/png',
                                'image/jpeg',
                            ],
                            'magicFile' => false,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
