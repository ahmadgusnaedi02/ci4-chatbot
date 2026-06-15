<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('admin_can')) {
    function admin_can(string $menuKey, string $action = 'view'): bool
    {
        return service('permissions')->can($menuKey, $action);
    }
}

if (!function_exists('admin_sidebar_menus')) {
    function admin_sidebar_menus(): array
    {
        return service('permissions')->getVisibleSidebarMenus();
    }
}
