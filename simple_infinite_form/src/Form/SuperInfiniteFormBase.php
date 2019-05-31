<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form to be extended by modules that need an "infinite" config form
 * with draggable elements. Still really simple, but with super results.
 *.
 * See the simple_infinite_form_example module for an example.
 */

abstract class SuperInfiniteFormBase extends InfiniteFormBase {

  /**
   * The form elements that should make up each row.
   * Note that you should NOT include anything about weight or tables.
   * That will happen automatically. Just put in the "mini form" that
   * goes in each row of the table. Include a '#header_text' to customize
   * the header. Do not enter any default_value or value either.
   *
   * Many input types are simple like... color, date, email, machine_name,
   * number, radio, radios, select, tel, textarea, textfield, url
   *
   * Checkboxes and Radios don't work well. Use select as an alternative.
   *
   * For other intput types, you may have to customize populateInfiniteValues.
   *
   */
  protected function baseElement($index = NULL) {
    return [
      'text' => [
        '#type' => 'textfield',
        '#size' => 60,
        '#header_text' => $this->t('Text'),
        '#required' => TRUE,
      ],
    ];
  }

  ///////////////////////////////////////////////////////////////////////////
  // When extending this class, you won't likely extend the functions below.
  // That's what makes this so simple.
  ///////////////////////////////////////////////////////////////////////////

  /**
   * Helper function to make a header for the table
   */
  protected function getHeader() {
    $header = [];
    foreach ($this->baseElement() as $key => $val) {
      $header[$key] = isset($val['#header_text']) ? $this->t($val['#header_text']) : $key;
    }
    $header['weight'] = $this->t('Weight');
    $header['delete'] = $this->t('Delete');
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function makeInfiniteValuesWrapper() {
    return [
      '#tree' => TRUE,
      '#type' => 'table',
      '#header' => $this->getHeader(),
      '#prefix' => '<div id="infinite-values-wrapper">',
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
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state) {
    $slots = $form_state->get('slots') ? $form_state->get('slots') : 0;
    $infinite_values = $form_state->get('infinite_values');
    for ($i = 0; $i < $slots; $i++) {
      $row = $this->baseElement($i);
      // add correct default value to each form element
      foreach ($row as $key => $element) {
        if (isset($infinite_values[$i])) {
          $row[$key]['#value'] = $infinite_values[$i][$key];
        }
      }
      //add some stuff to make it draggable
      $row['#attributes'] = [
        'class' => [
          'draggable',
        ],
      ];
      $row['weight'] = [
        '#type' => 'weight',
        '#default_value' => isset($form_state->getValue('infinite_values')[$i]['weight']) ? $form_state->getValue('infinite_values')[$i]['weight'] : 0,
        '#attributes' => array('class' => array('idcfb-weight')),
      ];
      $row['delete'] = $this->makeDeleteSlotButton($i);
      $form['infinite_values'][] = $row;
    }
  }
}
