<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Mail\Controller\Mail' => 'Mail\Controller\MailController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'mail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/mail[/:id][/:folder]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Mail\Controller\Mail',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => [
                    'dir' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/dir',
                            'defaults' => [
                                'action' => 'dir'
                            ]
                        ]
                    ],
                    'subdir' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/subdir/:folder',
                            'defaults' => [
                                'action' => 'subdir']
                        ]
                    ]
                ]
            ),
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
//            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
//            'index/index' => __DIR__ . '/../view/index/index.phtml',
//            'error/404' => __DIR__ . '/../view/error/404.phtml',
//            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'application' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);