<?php

    $config = array();

    Config::Set('router.page.ref', 'PluginReferals_ActionReferals');

    $config['widgets'][] = array(
        'name'     => 'referals',
        'group'    => 'right',
        'priority' => 50,
        'params'   => array('plugin' => 'referals'),
        'action'   => array(
            'profile'
        ),
    );

    return $config;

