<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\FormStateInterface;

interface InfiniteFormInterface {

  /**
   * @return array|null
   */
  public function makeInfiniteValuesWrapper();

  /**
   * @return array|null
   */
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state);

}
