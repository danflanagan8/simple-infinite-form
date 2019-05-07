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
   * url, entity_autocomplete (see getEntityType and getEntityBundles)
   *
   * Does not work for...
   * button, checkbox, checkboxes, color, datelist, datetime, file, language_select,
   * managed_file, password, password_confirm, path, radio, radios, range,
   * search, select, submit, table, tableselect, token, value, vertical_tabs
   * weight
   */
  protected function getInputType() {
    return 'textfield';
  }

  /**
   * Returns the entity type as a string.
   * Used only if the Input Type is entity_autocomplete
   */
  protected function getEntityType() {
    return 'node';
  }

  /**
   * Returns an array of allowed bundle types.
   * Used only if the Input Type is entity_autocomplete
   * If empty, all bundles will be allowed.
   */
  protected function getEntityBundles() {
    return array();
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
    $config = $this->config($this->getEditableConfigNames()[0]);
    $infinite_values = $form_state->cleanValues()->getValue('infinite_values');
    for($i = 0; $i < $slots ;$i++){
      $value = NULL;
      if(isset($infinite_values[$i])){
        if($this->getInputType() == 'entity_autocomplete'){
          $value = \Drupal::entityTypeManager()->getStorage($this->getEntityType())->load($infinite_values[$i]);
          $bundles = $this->getEntityBundles();
        }else{
          $value = $infinite_values[$i];
        }
      }
      $form['infinite_values'][$i][] = [
        '#type' => $this->getInputType(),
        '#target_type' => $this->getEntityType(),
        '#value' => $value,
        '#required' => FALSE,
      ];
      $form['infinite_values'][$i][] = $this->makeDeleteSlotButton($i);

      if(!empty($bundles)){
        $form['infinite_values'][$i]['#selection_settings'] = [
          'target_bundles' => $bundles,
        ];
      }
    }
  }

}
