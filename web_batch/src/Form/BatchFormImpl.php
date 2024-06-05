<?php

namespace Drupal\web_batch\Form;

use Drupal\web_batch\BatchFormBase;

/**
 * Implement batch form.
 * 
 */
class BatchFormImpl extends BatchFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_batch_form_impl';
  }

  public static function executeOperation($id, $context): string {
    // do something with the id and return the result of operation.
    return 'updated';
  }

  public function getAllItemIds($args): array {
    // return all the items to run batch against.
    return [];
  }
}
