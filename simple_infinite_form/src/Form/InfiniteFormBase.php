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

    $config = $this->config($this->getEditableConfigNames()[0]);

    $this->getSlots($form_state, $config);
    $form['intro'] = $this->makeIntro();
    $form['infinite_values'] = $this->makeInfiniteValuesContainer();
    $this->populateInfiniteValues($form, $form_state, $config);
    $form['add_slot'] = $this->makeAddSlotButton();

    return parent::buildForm($form, $form_state, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable($this->getEditableConfigNames()[0]);
    $rows = $form_state->getValue(['infinite_values']);
    $values_to_save = [];
    //strip away empty values. Be careful not to strip zero, though.
    foreach($rows as $row){
      if(!$this->rowIsEmpty($row)){
        $values_to_save[] = $row;
      }
    }
    $config->set($this->getConfigKeyName(), $values_to_save);
    $config->save();
    parent::submitForm($form, $form_state);
  }

  public function getSlots(FormStateInterface $form_state, $config) {
    //This block determines how many slots to show
    $slots = $form_state->get('slots');
    if ($slots == NULL) {
      if (!empty($config->get($this->getConfigKeyName()))) {
        $slots = count($config->get($this->getConfigKeyName())) + 1; //always have at least on open slot.
        $form_state->set('slots', $slots);
      }
      else {
        $slots = 1; //always have at least on open slot.
        $form_state->set('slots', $slots);
      }
    }
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

  public function addSlotSubmit(array &$form, FormStateInterface $form_state){
    $form_state->set('slots', $form_state->get('slots') + 1);
    $form_state->setRebuild(true);
  }

  public function addSlotCallback(array &$form, FormStateInterface $form_state){
    return $form['infinite_values'];
  }

}
