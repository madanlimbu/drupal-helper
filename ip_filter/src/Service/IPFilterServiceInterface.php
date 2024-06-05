<?php

namespace Drupal\ip_filter\Service;

interface IPFilterServiceInterface {

  /**
   * Check if current IP is blacklisted.
   *
   * @return bool
   */
  public function isBlacklistedIp(): bool;

}
