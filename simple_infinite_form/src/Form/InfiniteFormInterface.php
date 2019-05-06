<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\FormStateInterface;

interface InfiniteFormInterface {

  /**
   * @return array|null
   */
  public function makeInfiniteValuesContainer();

  /**
   * @return array|null
   */
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state);

  /**
   * An empty row is dropped from config when saving. See the submit function.
   *
   * @return boolean
   */
  public function rowIsEmpty($row);

}
