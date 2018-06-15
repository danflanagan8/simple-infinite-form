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
   */
  protected function getInputType() {
    return 'textfield';
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
      $form['infinite_values'][] = [
        '#type' => $this->getInputType(),
        '#default_value' => isset($config->get($this->getConfigKeyName())[$i]) ? $config->get($this->getConfigKeyName())[$i] : NULL,
        '#required' => FALSE,
      ];
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
    //strip away empty values
    foreach($values as $value){
      if($value !== ''){
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
