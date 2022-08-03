<?php
namespace Finance;

use Application\Controller\ControllerFactory;
use Travel\Controller\FinanceController;
use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'financeStatus' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/finance/status[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => FinanceStatus::class,
                        'action' => 'index'
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'financeStatus' => [
                [
                'label' => "Finance Status",
                'route' => "financeStatus"
            ],
                [
                'label' => "Finance Status",
                'route' => "financeStatus",
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'financeStatus',
                        'action' => 'index',
                    ],
                    //     [
                    //     'label' => 'Add',
                    //     'route' => 'travelStatus',
                    //     'action' => 'add',
                    // ],
                        [
                        'label' => 'Detail',
                        'route' => 'travelStatus',
                        'action' => 'view',
                    ],
                ],
            ],
        ],
    ],
    
    'controllers' => [
        'factories' => [
            Controller\FinanceStatus::class => ControllerFactory::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
