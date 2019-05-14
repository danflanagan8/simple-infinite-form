<?php

namespace Drupal\simple_infinite_form_example\Form;

use Drupal\simple_infinite_form\Form\SuperInfiniteFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An example of a form that extends the SuperInfiniteFormBase class.
 *
 * This is intentionally more complicated than the Speed Dial form.
 * One of the input elements is an entity_autocomplete element. In order for that
 * to work, we need to override the populateInfiniteValues function from
 * SuperInfiniteFormBase.
 *
 */

class FavoriteArticlesForm extends SuperInfiniteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'favorite_articles_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_infinite_form_example.favorite_articles',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigKeyName() {
    return 'articles';
  }

  /**
   * {@inheritdoc}
   */
  protected function baseElement($index = NULL) {
    return [
      'article' => [
        '#title' => 'Article',
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#size' => 40,
        '#selection_settings' => [
          'target_bundles' => [
            'article',
          ],
        ],
        '#required' => TRUE,
      ],
      'color' => [
        '#type' => 'color',
        '#required' => FALSE,
        '#description' => $this->t("Does this article make you feel a certain color?"),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function makeIntro() {
    return [
      '#markup' => '<div><p>Select your favorite articles featured on this site.
      This is an example of how to extend the SuperInfiniteFormBase class.
      I had trouble with the delete button, form state, and entity_autocomplete
      input elements. That is why the delete button only shows on the last row.</p></div>'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function populateInfiniteValues(array &$form, FormStateInterface $form_state) {
    $slots = $form_state->get('slots') ? $form_state->get('slots') : 0;
    $infinite_values = $form_state->get('infinite_values');
    for ($i = 0; $i < $slots; $i++) {
      $row = $this->baseElement();
      //add correct default value to each form element
      foreach ($row as $key => $element) {
        if (isset($infinite_values[$i][$key])) {
          if ($key == 'article') {
            $row[$key]['#default_value'] = \Drupal::entityTypeManager()->getStorage('node')->load($infinite_values[$i]['article']);
          }
          else {
            $row[$key]['#default_value'] = $infinite_values[$i][$key];
            $row[$key]['#value'] = $infinite_values[$i][$key];
          }
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
        '#value' => isset($infinite_values[$i]['weight']) ? $infinite_values[$i]['weight'] : 0,
        '#attributes' => array('class' => array('idcfb-weight')),
      ];

      //I can't get the delete button to work right
      //so I'm only putting it on the last row.
      //I don't understand entity_autocomplete very well.
      if ($i == ($slots - 1)) {
        $row['delete'] = $this->makeDeleteSlotButton($i);
      }
      else {
        $row['delete'] = $this->makeSpace();
      }

      $form['infinite_values'][] = $row;
    }
  }

}
