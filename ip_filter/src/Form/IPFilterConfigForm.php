<?php

namespace Drupal\ip_filter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ip_filter\Service\IPFilterConfigServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IPFilterConfigForm extends FormBase {

  /**
   * @var \Drupal\ip_filter\Service\IPFilterConfigServiceInterface
   */
  private $ipFilterConfig;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ip_filter_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function __construct(IPFilterConfigServiceInterface $ipFilterConfig) {
    $this->ipFilterConfig = $ipFilterConfig;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ip_filter.config'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->ipFilterConfig->getConfig();

    $form['ip_filter_blacklist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Blacklisted IP'),
      '#default_value' => $config['ip_filter_blacklist'],
      '#description' => $this->t('Enter a list of IP addresses, Add one IP per line (Make sure to remove any empty space around it).'),
      '#rows' => 10,
      '#cols' => 30,
    ];

    $form['ip_filter_blacklist_path'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Blacklisted Path'),
      '#default_value' => $config['ip_filter_blacklist_path'],
      '#description' => $this->t('Enter a list of path to use when filtering IP, Add one path per line (Always start with a slash "/", i.e "/contact-us" ).'),
      '#rows' => 10,
      '#cols' => 30,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->ipFilterConfig->setConfig([
      'ip_filter_blacklist' => $form_state->getValue('ip_filter_blacklist'),
      'ip_filter_blacklist_path' => $form_state->getValue('ip_filter_blacklist_path'),
    ]);
    $this->messenger()->addStatus($this->t('The configuration options have been saved.'));
  }

}
