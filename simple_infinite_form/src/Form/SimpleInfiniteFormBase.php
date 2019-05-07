<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form to be extended by modules that need a simple "infinite" config form.
 * See the simple_infinite_form_example module for an example.
 */

abstract class SimpleInfiniteFormBase extends InfiniteFormBase {

  /**
   * Returns the field type as a string
   *
   * Should work well for...
   * date, email, machine_name, number, tel, textarea, textfield,
   * url
   *
   * Does not work for...
   * button, checkbox, checkboxes, color, datelist, datetime, file, language_select,
   * managed_file, password, password_confirm, path, radio, radios, range,
   * search, select, submit, table, tableselect, token, value, vertical_tabs
   * weight, entity_autocomplete
   */
  protected function getInputType() {
    return 'textfield';
  }

  ///////////////////////////////////////////////////////////////////////////
  // When extending this class, you won't likely extend the functions below.
  // That's what makes this so simple.
  ///////////////////////////////////////////////////////////////////////////

  /**
   * {@inheritdoc}
   */
  public function makeInfiniteValuesWrapper() {
    return [
      '#tree' => TRUE,
      '#type' => 'table',
      '#prefix' => '<div id="infinite-values-wrapper"><h2>'.ucfirst($this->getConfigKeyName()).'</h2>',
      '#suffix' => '</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state) {
    $slots = $form_state->get('slots') ? $form_state->get('slots') : 0;
    $infinite_values = $form_state->get('infinite_values');
    for ($i = 0; $i < $slots; $i++) {
      $value = isset($infinite_values[$i]['input']) ? $infinite_values[$i]['input'] : NULL;
      $form['infinite_values'][$i] = [
        '#type' => 'container',
      ];
      $form['infinite_values'][$i]['input'] = [
        '#type' => $this->getInputType(),
        '#required' => TRUE,
      ];
      if (isset($infinite_values[$i]['input'])) {
        $form['infinite_values'][$i]['input']['#default_value'] = $infinite_values[$i]['input'];
        $form['infinite_values'][$i]['input']['#value'] = $infinite_values[$i]['input'];
      }
      $form['infinite_values'][$i]['delete'] = $this->makeDeleteSlotButton($i);
    }
  }

}
