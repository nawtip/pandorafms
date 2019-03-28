<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2010 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// Load global vars
global $config;

check_login();

enterprise_hook('open_meta_frame');

require_once $config['homedir'].'/include/functions_profile.php';
require_once $config['homedir'].'/include/functions_users.php';
require_once $config['homedir'].'/include/functions_groups.php';
require_once $config['homedir'].'/include/functions_visual_map.php';

$meta = false;
if (enterprise_installed() && defined('METACONSOLE')) {
    $meta = true;
}

$id = get_parameter_get('id', $config['id_user']);
// ID given as parameter
$status = get_parameter('status', -1);
// Flag to print action status message
$user_info = get_user_info($id);
$id = $user_info['id_user'];
// This is done in case there are problems with uppercase/lowercase (MySQL auth has that problem)
if ((!check_acl($config['id_user'], users_get_groups($id), 'UM'))
    and ($id != $config['id_user'])
) {
    db_pandora_audit('ACL Violation', 'Trying to view a user without privileges');
    include 'general/noaccess.php';
    exit;
}

// If current user is editing himself or if the user has UM (User Management) rights on any groups the user is part of AND the authorization scheme allows for users/admins to update info
if (($config['id_user'] == $id || check_acl($config['id_user'], users_get_groups($id), 'UM')) && $config['user_can_update_info']) {
    $view_mode = false;
} else {
    $view_mode = true;
}

$urls = [];
if (is_metaconsole()) {
    user_meta_print_header();
    $urls['main'] = 'index.php?sec=advanced&amp;sec2=advanced/users_setup&amp;tab=user_edit';
} else {
    $urls['main'] = 'index.php?sec=workspace&amp;sec2=operation/users/user_edit';
    $urls['notifications'] = 'index.php?sec=workspace&amp;sec2=operation/users/user_edit_notifications';
    $buttons = [
        'main'          => [
            'active' => $_GET['sec2'] === 'operation/users/user_edit',
            'text'   => "<a href='{$urls['main']}'>".html_print_image(
                'images/user_green.png',
                true,
                ['title' => __('User management')]
            ).'</a>',
        ],
        'notifications' => [
            'active' => $_GET['sec2'] === 'operation/users/user_edit_notifications',
            'text'   => "<a href='{$urls['notifications']}'>".html_print_image(
                'images/alerts_template.png',
                true,
                ['title' => __('User notifications')]
            ).'</a>',
        ],
    ];

    ui_print_page_header(
        __('User detail editor'),
        'images/op_workspace.png',
        false,
        '',
        false,
        $buttons
    );
}
