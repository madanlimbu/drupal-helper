<?php

namespace Drupal\ip_filter\Service;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\AdminContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service that validates the IP access.
 *
 */
class IPFilterService implements IPFilterServiceInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\ip_filter\Service\IPFilterConfigServiceInterface
   */
  private $ipFilterConfig;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * @var string|null
   */
  private $currentUserIp;

  /**
   * @var string|null
   */
  private $currentPath;

  /**
   *
   */
  public function __construct(IPFilterConfigServiceInterface $ipFilterConfig, ModuleHandlerInterface $moduleHandler, AdminContext $adminContext, RequestStack $requestStack) {
    $this->ipFilterConfig = $ipFilterConfig;
    $this->moduleHandler = $moduleHandler;
    $this->adminContext = $adminContext;
    $this->currentUserIp = $requestStack->getCurrentRequest()->getClientIp();
    $this->currentPath = $requestStack->getCurrentRequest()->getPathInfo();
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ip_filter.config'),
      $container->get('module_handler'),
      $container->get('router.admin_context'),
      $container->get('request_stack'),
    );
  }

  /**
   * Currently just checks against local IP list.
   *
   * @return bool
   */
  public function isBlacklistedIp(): bool {
    $data = [
      'current_user_ip' => $this->currentUserIp,
      'current_path' => $this->currentPath,
      'is_blocked' => FALSE,
    ];

    if ($this->isProtectedPath($this->currentPath)) {
      $this->checkLocalDb($data);
    }

    $this->checkCustomSource($data);

    return $data['is_blocked'];
  }

  /**
   * Only specific path to be used for filtering.
   *
   */
  public function isProtectedPath($path): bool {
    if ($this->adminContext->isAdminRoute()) {
      // We don't want to block admin route for now.
      return FALSE;
    }

    $blacklist_path = $this->extractConfig('ip_filter_blacklist_path');
    if (empty($blacklist_path)) {
      // If empty assuming all path should be protected.
      return TRUE;
    }
    elseif (in_array($path, $blacklist_path)) {
      // If only custom path is protected, check against them.
      return TRUE;
    }
    else {
      // Not in custom protected path list.
      return FALSE;
    }
  }

  /**
   * Custom local IP list checklist.
   *
   */
  public function checkLocalDb(&$data) {
    $ip_blacklist = $this->extractConfig('ip_filter_blacklist');
    if (!empty($ip_blacklist) && !empty($this->currentUserIp) && in_array($this->currentUserIp, $ip_blacklist)) {
      $data['is_blocked'] = TRUE;
    }

    return $data;
  }

  /**
   *
   */
  public function extractConfig($configId): array|bool {
    $config = $this->ipFilterConfig->getConfig()[$configId] ?? '';
    return empty($config) ? [] : preg_split("/\r\n|\n|\r/", $config);
  }

  /**
   * Let third party module hook into our checklist.
   *
   */
  public function checkCustomSource(&$data): void {
    $this->moduleHandler->invokeAll('ip_filter_allowed_ip_alter', [&$data]);
  }

}
