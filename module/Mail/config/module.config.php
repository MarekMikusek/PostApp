<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Mail\Controller\Mail' => 'Mail\Controller\MailController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'home' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/[:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Mail\Controller\Mail',
                        'action'=>'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'mail' => __DIR__ . '/../view',
        ),
    ),
);