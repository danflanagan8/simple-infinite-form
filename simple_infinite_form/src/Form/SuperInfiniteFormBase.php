<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form to be extended by modules that need an "infinite" config form
 * with draggable elements.
 *.
 * See the simple_infinite_form_example module for an example.
 */


abstract class SuperInfiniteFormBase extends InfiniteFormBase {

  /**
   * The form elements that should make up each row.
   * Note that you should not include anything about weight or tables.
   * That will happen automatically. Just put in the "mini form" that
   * goes in each row of the table. Include a '#header_text' for custom header.
   *
   */
  protected function baseElement() {
    return [
      'text' => [
        '#type' => 'textfield',
        '#size' => 60,
        '#header_text' => $this->t('Text'),
      ],
    ];
  }

  /**
   * If a key is here, that input is not considered during this form's extra validation.
   * However, it IS considered during rowIsEmpty.
   */
  protected function notRequired() {
    return [];
  }

  /**
   * If a key is here, the value is ignored during rowIsEmpty.
   * This is useful for checkbox inputs among other things.
   * "Weight" is treated as neverEmpty in the rowIsEmpty function.
   */
  protected function neverEmpty() {
    return [];
  }

  ///////////////////////////////////////////////////////////////////////////
  // When extending this class, you won't likely extend the functions below.
  // That's what makes this so simple.
  ///////////////////////////////////////////////////////////////////////////

  /**
  * {@inheritdoc}
  */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $values = $form_state->getValue(['infinite_values']);
    foreach($values as $index => $row){
      if (!$this->rowIsEmpty($row)) {
        foreach ($row as $key => $val) {
          if ($val === '' || $val === NULL) {
            if (!in_array($key, $this->notRequired())) {
              $form_state->setErrorByName("infinite_values][$index][$key", "$key is required.");
            }
          }
        }
      }
    }
  }

  /**
   * Helper function to make a header for the table
   */
  protected function getHeader() {
    $header = [];
    foreach ($this->baseElement() as $key => $val) {
      $header[$key] = isset($val['#header_text']) ? $this->t($val['#header_text']) : $key;
    }
    $header['weight'] = $this->t('Weight');
    return  $header;
  }

  /**
   * {@inheritdoc}
   */
  public function makeInfiniteValuesContainer() {
    return [
      '#tree' => TRUE,
      '#type' => 'table',
      '#header' => $this->getHeader(),
      '#prefix' => '<div id="infinite-values-wrapper"><h2>'.ucfirst($this->getConfigKeyName()).'</h2>',
      '#suffix' => '</div>',
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'idcfb-weight',
        ],
      ],
      '#attributes' => ['id' => 'infinite-values-table'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state, $config) {
    $slots = $form_state->get('slots') ? $form_state->get('slots') : 0;
    for ($i = 0; $i < $slots; $i++) {
      $row = $this->baseElement();
      //add correct default value to each form element
      foreach ($row as $key => $element) {
        $row[$key]['#default_value'] = isset($config->get($this->getConfigKeyName())[$i][$key]) ? $config->get($this->getConfigKeyName())[$i][$key] : NULL;
      }
      //add some stuff to make it draggable
      $row['#attributes'] = [
        'class' => [
          'draggable',
        ],
      ];
      $row['weight'] = [
        '#type' => 'weight',
        '#default_value' => isset($config->get($this->getConfigKeyName())[$i]['weight']) ? $config->get($this->getConfigKeyName())[$i]['weight'] : 0,
        '#attributes' => array('class' => array('idcfb-weight')),
      ];
      $form['infinite_values'][] = $row;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rowIsEmpty($row) {
    foreach ($row as $key => $val) {
      if ($key != 'weight' && !in_array($key, $this->neverEmpty())) {
        if($val !== '' && $val !== NULL){
          return FALSE;
        }
      }
    }
    return TRUE;
  }
}
