<?php

namespace Drupal\ip_filter\EventSubscriber;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\ip_filter\Service\IPFilterServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class IPFilterEventSubscriber implements EventSubscriberInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\ip_filter\Service\IPFilterServiceInterface
   */
  protected $ipFilterService;

  /**
   *
   */
  public function __construct(IPFilterServiceInterface $ipFilterService) {
    $this->ipFilterService = $ipFilterService;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ip_filter.service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['blockBlacklistedIp'];
    return $events;
  }

  /**
   * Callback to actual service that validates IP.
   *
   */
  public function blockBlacklistedIp(RequestEvent $event): void {
    if ($this->ipFilterService->isBlacklistedIp()) {
      $event->setResponse(new Response('Your IP address has been blocked. Contact site admin to unban it.', 403));
    }
  }

}
