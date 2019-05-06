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
  public function makeInfiniteValuesContainer() {
    return [
      '#tree' => TRUE,
      '#type' => 'container',
      '#prefix' => '<div id="infinite-values-wrapper"><h2>'.ucfirst($this->getConfigKeyName()).'</h2>',
      '#suffix' => '</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state, $config) {
    $slots = $form_state->get('slots') ? $form_state->get('slots') : 0;
    for($i = 0; $i < $slots ;$i++){
      $default = NULL;
      if(isset($config->get($this->getConfigKeyName())[$i])){
        if($this->getInputType() == 'entity_autocomplete'){
          $default = \Drupal::entityTypeManager()->getStorage($this->getEntityType())->load($config->get($this->getConfigKeyName())[$i]);
          $bundles = $this->getEntityBundles();
        }else{
          $default = $config->get($this->getConfigKeyName())[$i];
        }
      }
      $form['infinite_values'][$i] = [
        '#type' => $this->getInputType(),
        '#target_type' => $this->getEntityType(),
        '#default_value' => $default,
        '#required' => FALSE,
      ];
      if(!empty($bundles)){
        $form['infinite_values'][$i]['#selection_settings'] = [
          'target_bundles' => $bundles,
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rowIsEmpty($row){
    if($row !== '' && $row !== NULL){
      return FALSE;
    }
    return TRUE;
  }

}
