<?php

namespace Drupal\simple_infinite_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to be extended by modules that need a simple "infinite" config form.
 * See SuperInfiniteFormBase and SimpleInfiniteFormBase.
 */

abstract class InfiniteFormBase extends ConfigFormBase implements InfiniteFormInterface {

  /**
   * Returns the array key that should be used for config.
   * Will be displayed above the fields also.
   */
  protected function getConfigKeyName() {
    return 'values';
  }

  /**
   * Intended to be used to add help text to the top of a form.
   */
  protected function makeIntro() {
    return NULL;
  }

  ///////////////////////////////////////////////////////////////////////////
  // When extending this class, you won't likely extend the functions below.
  // That's what makes this so simple.
  ///////////////////////////////////////////////////////////////////////////

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->initForm($form_state);
    $form['intro'] = $this->makeIntro();
    $form['infinite_values'] = $this->makeInfiniteValuesWrapper();
    $this->populateInfiniteValues($form, $form_state);
    $form['add_slot'] = $this->makeAddSlotButton();

    return parent::buildForm($form, $form_state);
  }

  public function initForm(FormStateInterface $form_state){
    if ($form_state->get('already_initialized')) {
      return;
    }
    $config = $this->config($this->getEditableConfigNames()[0]);
    if (!empty($config->get($this->getConfigKeyName()))) {
      $form_state->setValue('infinite_values', $config->get($this->getConfigKeyName()));
      $slots = count($config->get($this->getConfigKeyName()));
      $form_state->set('slots', $slots);
    }
    else {
      $form_state->set('slots', 1);
    }
    $form_state->set('already_initialized', TRUE);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable($this->getEditableConfigNames()[0]);
    $infinite_values = $form_state->cleanValues()->getValue('infinite_values');
    $values_to_save = [];
    if (!empty($infinite_values)) {
      foreach ($infinite_values as $key => $val) {
        if (count($val) == 1) {
          //unpack unneeded nesting
          $values_to_save[] = $val;
        }
        else {
          $values_to_save[] = $infinite_values[$key];
        }
      }
    }
    $config->set($this->getConfigKeyName(), $values_to_save);
    $config->save();
    parent::submitForm($form, $form_state);
  }

  public function makeAddSlotButton() {
    return [
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
  }

  public function makeDeleteSlotButton($i) {
    return [
      '#type' => 'submit',
      '#value' => 'Delete Slot',
      '#id' => "delete_slot-$i",
      '#name' => "delete_slot-$i", //doesn't work without a name!
      '#submit' => ['::deleteSlotSubmit'],
      '#ajax' => [
        'callback' => [$this, 'deleteSlotCallback'],
        'event' => 'click',
        'wrapper' => 'infinite-values-wrapper',
      ],
    ];
  }

  public function addSlotSubmit(array &$form, FormStateInterface $form_state){
    $form_state->set('slots', $form_state->get('slots') + 1);
    $form_state->setRebuild(true);
  }

  public function addSlotCallback(array &$form, FormStateInterface $form_state){
    drupal_set_message('You have unsaved changes', 'warning');
    return $form['infinite_values'];
  }

  public function deleteSlotSubmit(array &$form, FormStateInterface $form_state){
    $id = $form_state->getTriggeringElement()['#id'];
    $index = explode("-", $id)[1];
    $infinite_values = $form_state->getValue('infinite_values');
    array_splice($infinite_values, $index, 1);
    $infinite_values = $form_state->setValue('infinite_values', $infinite_values);
    $form_state->set('slots', $form_state->get('slots') - 1);
    $form_state->setRebuild(true);
  }

  public function deleteSlotCallback(array &$form, FormStateInterface $form_state){
    drupal_set_message('You have unsaved changes', 'warning');
    return $form['infinite_values'];
  }

}
