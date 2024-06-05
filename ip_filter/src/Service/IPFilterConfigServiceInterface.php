<?php

namespace Drupal\ip_filter\Service;

interface IPFilterConfigServiceInterface {

  /**
   *  Return config related to ip filtering.
   *
   * @return array
   *   The config values as associative array.
   *   - 'ip_filter_blacklist': List of blacklisted IP.
   *   - 'ip_filter_blacklist_path': List of protected path.
   */
  public function getConfig(): array;

  /**
   *
   * @param array $config
   *   The config values as associative array.
   *   - 'ip_filter_blacklist': List of blacklisted IP.
   *   - 'ip_filter_blacklist_path': List of protected path.
   * @return void
   */
  public function setConfig(array $config): void;

}
