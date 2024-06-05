<?php

namespace Drupal\web_batch;


interface BatchFormInterface {

  /**
   * Get all the list of items to run batch process against.
   *
   * @param $args
   *
   * @return array
   */
  function getAllItemIds($args): array;

  /**
   * Run single operation on given id.
   * return status of operation "updated" | "ignored" | "failed"
   * 
   * @param $id
   * @param $context
   *
   * @return string
   */
  public static function executeOperation($id, $context): string;

}