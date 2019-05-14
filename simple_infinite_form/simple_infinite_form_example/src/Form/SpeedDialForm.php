<?php

namespace Drupal\simple_infinite_form_example\Form;

use Drupal\simple_infinite_form\Form\SuperInfiniteFormBase;

/**
 * An example of a form that extends the SuperInfiniteFormBase class.
 *
 * In most cases, these are the only functions you will have to declare.
 *
 */

class SpeedDialForm extends SuperInfiniteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speed_dial_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_infinite_form.speed_dial',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigKeyName() {
    return 'entries';
  }

  /**
   * {@inheritdoc}
   */
  protected function baseElement($index = NULL) {
    return [
      'name' => [
        '#type' => 'textfield',
        '#size' => 30,
        '#title' => 'Name',
        '#header_text' => 'Name',
        '#required' => TRUE,
      ],
      'number' => [
        '#type' => 'tel',
        '#title' => 'Phone Number',
        '#header_text' => 'Phone',
        '#required' => TRUE,
      ],
      'notes' => [
        '#type' => 'textarea',
        '#title' => 'Notes',
        '#rows' => 3,
        '#cols' => 15,
        '#header_text' => 'Notes',
        '#required' => FALSE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function makeIntro() {
    return [
      '#markup' => $this->t('Just like in that one episode on Seinfeld, keep
                             track of your most-used numbers and order them
                             based on how much you like the person.'),
    ];
  }

}
