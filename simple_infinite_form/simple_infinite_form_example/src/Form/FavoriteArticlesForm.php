<?php

namespace Drupal\simple_infinite_form_example\Form;

use Drupal\simple_infinite_form\Form\SimpleInfiniteFormBase;

/**
 * An example of a form that extends the SimpleInfiniteFormBase class.
 * You definitely want your extnesion to declare getFormId and getEditableConfigNames.
 * If the default versions of getConfigKeyName and getInputType don't work
 * for your purposes, declare those. This example declares a build function that adds
 * markup to the top of the form. You don't necessarily need to do that. The parent
 * build function might be all you need. You could potentially add input fields to
 * the form in a build function. That would also require a new declaration of
 * the submit function.
 *
 * This example also declares getEntityType and getEntityBundles to better
 * leverage the entity_autocomplete Input Type.
 */

class FavoriteArticlesForm extends SimpleInfiniteFormBase {

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
  protected function getInputType() {
    return 'entity_autocomplete';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityType() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityBundles() {
    return array('article');
  }

  /**
   * {@inheritdoc}
   */
  protected function makeIntro() {
    return [
      '#markup' => '<div><p>Select your favorite articles featured on this site. This is an example of how to extend the SimpleInfiniteFormBase class.</p></div>'
    ];
  }

}
