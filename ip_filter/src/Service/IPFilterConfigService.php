<?php

namespace Drupal\ip_filter\Service;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get configurations.
 *
 * Currently, uses simple state.
 * It can be switched to config entities later if needed.
 *
 */
class IPFilterConfigService implements IPFilterConfigServiceInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   *
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
    );
  }

  /**
   * Return config.
   *
   */
  public function getConfig(): array {
    return [
      'ip_filter_blacklist' => $this->state->get('ip_filter_blacklist'),
      'ip_filter_blacklist_path' => $this->state->get('ip_filter_blacklist_path'),
    ];
  }

  /**
   * Set config on state.
   *
   * @param array $config
   * @return void
   */
  public function setConfig(array $config): void {
    $this->state->set('ip_filter_blacklist', $config['ip_filter_blacklist']);
    $this->state->set('ip_filter_blacklist_path', $config['ip_filter_blacklist_path']);
  }

}
