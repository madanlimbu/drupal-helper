<?php

/**
 * @file
 * Documentation for ip_filter API.
 */

/**
 * Alter if current user IP is blocked/allowed.
 *
 * @param array &$data
 *   An associative array containing current user IP address
 *   & status of it (Blocked/Allowed):
 *   - 'current_user_ip': The current user IP.
 *   - 'current_path': The path being requested.
 *   - 'is_blocked': Boolean to state if the IP is blocked.
 *
 *   Example use-case where we only want to block specific path.
 * @code
 *   if ($data['current_path'] !== '/contact-us') {
 *    $data['is_blocked'] = FALSE;
 *   }
 * @endcode
 *
 */
function hook_ip_filter_allowed_ip_alter(array &$data) {
}
