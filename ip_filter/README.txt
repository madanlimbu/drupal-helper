IP Filter

Lightweight Drupal module to blacklist & block specific IPs within Drupal.

Configuration available on - `admin/config/ip_filter`

Todo:
Note: This should be only used for small list of IP address's.
If we want to check against large IP list. We should extend/replace the service class or use hook (```hook_ip_filter_allowed_ip_alter```) provided to handle external API call to check IP against publicly blacklisted IP instead.
