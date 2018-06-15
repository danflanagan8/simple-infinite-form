<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to be extended by modules that need a simple "infinite" config form.
 * See the simple_infinite_form_example module for an example.
 */

abstract class SimpleInfiniteFormBase extends ConfigFormBase {

  /**
   * Returns the array key that should be used for config.
   * Will be displayed above the fields also.
   */
  protected function getConfigKeyName() {
    return 'values';
  }

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

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    //This block determines how many slots to show
    $slots = $form_state->get('slots');
    $config = $this->config($this->getEditableConfigNames()[0]);
    if($slots === NULL){
      $slots = count($config->get($this->getConfigKeyName())) + 1; //always have at least on open slot.
      $form_state->set('slots', $slots);
    }

    //This block puts the fields into the form along with default values from config
    $form['infinite_values'] = [
      '#tree' => TRUE,
      '#type' => 'container',
      '#prefix' => '<div id="infinite-values-wrapper"><h2>'.ucfirst($this->getConfigKeyName()).'</h2>',
      '#suffix' => '</div>',
    ];
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

    //The add slot button.
    $form['add_slot'] = [
      '#type' => 'submit',
      '#value' => 'Add Slot',
      '#id' => 'add_slot',
      '#submit' => ['::addSlotSubmit'],
      '#ajax' => [
        'callback' => [$this, 'addSlotCallback'],
        'event' => 'click',
        'wrapper' => 'infinite-values-wrapper',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable($this->getEditableConfigNames()[0]);
    $values = $form_state->getValue(['infinite_values']);
    $values_to_save = [];
    //strip away empty values. Be careful not to strip zero, though.
    foreach($values as $value){
      if($value !== '' && $value !== NULL){
        $values_to_save[] = $value;
      }
    }
    $config->set($this->getConfigKeyName(), $values_to_save);
    $config->save();
    parent::submitForm($form, $form_state);
  }

  public function addSlotSubmit(array &$form, FormStateInterface $form_state){
    $form_state->set('slots', $form_state->get('slots') + 1);
    $form_state->setRebuild(true);
  }

  public function addSlotCallback(array &$form, FormStateInterface $form_state){
    return $form['infinite_values'];
  }

}
