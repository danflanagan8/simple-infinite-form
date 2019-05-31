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
    $form['make_space'] = $this->makeSpace();

    return parent::buildForm($form, $form_state);
  }

  /**
   * Adds config values to form_state the first time buildForm runs.
   * If there is no config, this assumes we will want to show a single emtpy row.
   */
  public function initForm(FormStateInterface $form_state){
    if ($form_state->get('infinite_values') !== NULL) {
      return;
    }
    $config = $this->config($this->getEditableConfigNames()[0]);
    if (!empty($config->get($this->getConfigKeyName()))) {
      $infinite_values = $config->get($this->getConfigKeyName());
      if (!empty($infinite_values)) {
        foreach ($infinite_values as $key => $row) {
          if (!is_array($row)) {
            $infinite_values[$key] = ['input' => $row];
          }
        }
      }
      $form_state->set('infinite_values', $infinite_values);
      $form_state->setValue('infinite_values', $infinite_values);
      $slots = count($config->get($this->getConfigKeyName()));
      $form_state->set('slots', $slots);
    }
    else {
      $form_state->set('infinite_values', []);
      $form_state->set('slots', 1);
    }
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
          if (is_array($val)) {
            $values_to_save[] = reset($val);
          }
          else {
            $values_to_save[] = $val;
          }
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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getTriggeringElement()['#id'];
    if (strpos($id, "delete_slot") > -1 || strpos($id, "add_slot") > -1) {
      $form_state->clearErrors();
      return;
    }
    parent::validateForm($form, $form_state);
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
      '#attributes' => [
        'class' => [
          'button--danger',
        ]
      ],
      '#ajax' => [
        'callback' => [$this, 'deleteSlotCallback'],
        'event' => 'click',
        'wrapper' => 'infinite-values-wrapper',
      ],
    ];
  }

  /**
   * I think it looks nicer if there's a little space between the "Add Slot" button
   * and the "Save configuration" button. That's all this does.
   */
  public function makeSpace() {
    return [
      '#markup' => '<p>&nbsp;</p>',
    ];
  }

  public function addSlotSubmit(array &$form, FormStateInterface $form_state){
    $infinite_values = $form_state->getValue('infinite_values');
    $form_state->set('infinite_values', $infinite_values);
    $form_state->set('slots', $form_state->get('slots') + 1);
    $form_state->setRebuild(true);
  }

  public function addSlotCallback(array &$form, FormStateInterface $form_state){
    drupal_set_message('You have unsaved changes', 'warning');
    return $form['infinite_values'];
  }

  /**
   * Removes the unwanted data from $form_state. Note that wee can't just unset
   * the row we want to delete; rather, we need to splice it. Otherwise we would
   * end up with an empty row in the middle of the form.
   */
  public function deleteSlotSubmit(array &$form, FormStateInterface $form_state){
    $id = $form_state->getTriggeringElement()['#id'];
    $index = explode("-", $id)[1];
    $infinite_values = $form_state->getValue('infinite_values');
    array_splice($infinite_values, $index, 1);
    $form_state->setValue('infinite_values', $infinite_values);
    $form_state->set('slots', $form_state->get('slots') - 1);
    $form_state->set('infinite_values', $infinite_values);
    $form_state->setRebuild(true);
  }

  public function deleteSlotCallback(array &$form, FormStateInterface $form_state){
    drupal_set_message('You have unsaved changes', 'warning');
    return $form['infinite_values'];
  }

}
