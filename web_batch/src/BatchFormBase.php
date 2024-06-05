<?php

namespace Drupal\web_batch;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class BatchFormBase extends FormBase implements BatchFormInterface {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['ids'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ids'),
      '#description' => $this->t('Choose specific ids.'),
    ];
    $form['limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#description' => $this->t('Limit how many item in total to process.'),
    ];
    $form['chunk_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chunk size'),
      '#description' => $this->t('Set custom chunk size for each batch process. (5 by default)'),
    ];

    $form['message'] = [
      '#markup' => $this->t('Click run batch button bellow to start batch process.'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run Batch'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $ids = !empty($form_state->getValue('ids')) ? $form_state->getValue('ids') : NULL;
    $limit = !empty($form_state->getValue('limit')) ? $form_state->getValue('limit') : NULL;
    $chunk_size = !empty($form_state->getValue('chunk_size')) ? $form_state->getValue('chunk_size') : NULL;
    $args = [
      'ids' => $ids ? explode(",", $ids) : NULL,
      'limit' => $limit,
      'chunk_size' => $chunk_size,
    ];

    $this->createBatch($args);
    $this->messenger()->addStatus($this->t('Finished batch process.'));
  }

  /**
   * Create batch operation.
   *
   */
  protected function createBatch($args = []) {
    $allItemsIds = $this->getAllItemIds($args);

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t("Batch process."))
      ->setInitMessage('Starting batch process.')
      ->setProgressMessage('Batch in progress....')
      ->setErrorMessage('Batch has encountered an error.')
      ->setFinishCallback([self::class, 'addBatchFinished']);

    if (!empty($args['limit'])) {
      $allItemsIds = array_slice($allItemsIds, 0, $args['limit']);
    }

    $chunk_size = !empty($args['chunk_size']) ? $args['chunk_size']: 5;
    $chunks = array_chunk($allItemsIds, $chunk_size);
    $num_chunks = count($chunks);

    for ($batch_id = 0; $batch_id < $num_chunks; $batch_id++) {
      $batch_builder->addOperation(
        [self::class, 'addBatchOperation'],
        [
          [
            'ids' => $chunks[$batch_id],
            'batch_id' => $batch_id+1,
            'total' =>  count($allItemsIds),
            'opClass' => get_called_class(),
          ],
        ]
      );
    }

    batch_set($batch_builder->toArray());
  }

  /**
   * Setup batch operation for each chunks.
   *
   * @param $batch
   * @param $context
   *
   * @return void
   */
  public static function addBatchOperation($batch, &$context) {
    $ids = $batch['ids'];
    $total = $batch['total'];

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['previous_node'] = 0;
      $context['sandbox']['max'] = $total;
    }

    $result = $ids;
    foreach ($result as $row) {
      try {
        $result = call_user_func_array($batch['opClass'] . '::executeOperation', [$row, $context]);
        if ($result == 'updated') {
          $context['results']['success'][] = $row;
        } else {
          $context['results']['ignored'][] = $row;
        }
      } catch (\Exception $e) {
        $context['results']['failed'][] = $row;
      }

      $context['results']['total'][] = $row;
      $context['sandbox']['progress']++;
      $context['sandbox']['previous_node'] = $row;
    }
  }

  /**
   * Callback upon batch process fin.
   *
   * @param bool $success
   * @param array $results
   * @param array $operations
   * @param string $elapsed
   *
   * @return void
   */
  public static function addBatchFinished (bool $success, array $results, array $operations, string $elapsed) {
    if ($success) {
      $message = t("@count items were processed (@elapsed). (Success: @success, Ignored: @ignored and Failed: @failed)", [
        '@count' => isset($results['total']) ? count($results['total']) : 0,
        '@elapsed' => $elapsed,
        '@success' => isset($results['success']) ? count($results['success']) : 0,
        '@ignored' => isset($results['ignored']) ? count($results['ignored']) : 0,
        '@failed' => isset($results['failed']) ? count($results['failed']) : 0,
      ]);
      \Drupal::messenger()->addStatus($message);
      \Drupal::logger('web_batch')->info($message);
      \Drupal::logger('web_batch')->info('Batch complete result: ' . json_encode($results));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      \Drupal::messenger()->addError($message);
      \Drupal::logger('web_batch')->error($message);
      \Drupal::logger('web_batch')->error('Failed updating items, Batch complete result: ' . json_encode($results));
    }
  }

}