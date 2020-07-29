<?php

namespace Drupal\bibcite_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Reference edit forms.
 *
 * @ingroup bibcite_entity
 */
class ReferenceForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Try to restore from temp store, this must be done before calling.
    /** @var \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory */
    $tempStoreFactory = \Drupal::getContainer()->get('tempstore.private');
    $store = $tempStoreFactory->get('bibcite_reference_preview');

    // Attempt to load from preview when the uuid is present unless we are
    // rebuilding the form.
    $request_uuid = \Drupal::request()->query->get('uuid');

    if (!$form_state->isRebuilding() && $request_uuid && $preview = $store->get($request_uuid)) {
      /** @var \Drupal\Core\Form\FormStateInterface $preview */
      $form_state->setStorage($preview->getStorage());
      $form_state->setUserInput($preview->getUserInput());

      // Rebuild the form.
      $form_state->setRebuild();

      // The combination of having user input and rebuilding the form means
      // that it will attempt to cache the form state which will fail if it is
      // a GET request.
      $form_state->setRequestMethod('POST');

      $entity = $preview->getFormObject()->getEntity();
      $entity->inPreview = NULL;

      $form_state->set('has_been_previewed', TRUE);
    }
    $form = parent::form($form, $form_state);
    $operation = $this->getOperation();
    $form['#title'] = $this->t('<em>@operation @type</em> @title', [
      '@operation' => $operation !== 'default' ? ucfirst($operation) : $this->t('Create'),
      '@type' => $this->getBundleEntity()->label(),
      '@title' => $this->getEntity()->label(),
    ]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $reference = $this->entity;
    $preview_mode = $reference->type->entity->getPreviewMode();

    $element['submit']['#access'] = $preview_mode != DRUPAL_REQUIRED || $form_state->get('has_been_previewed');

    $element['preview'] = [
      '#type' => 'submit',
      '#access' => $preview_mode != DRUPAL_DISABLED && ($reference->access('create') || $reference->access('update')),
      '#value' => t('Preview'),
      '#weight' => 20,
      '#submit' => ['::submitForm', '::preview'],
    ];

    if (array_key_exists('delete', $element)) {
      $element['delete']['#weight'] = 100;
    }

    return $element;
  }

  /**
   * Custom preview submit handler for Reference.
   *
   * @param array $form
   *   Nested array of form elements that comprise the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function preview(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory */
    $tempStoreFactory = \Drupal::getContainer()->get('tempstore.private');
    $store = $tempStoreFactory->get('bibcite_reference_preview');

    /** @var \Drupal\bibcite_entity\Entity\Reference $entity */
    $entity = $form_state->getFormObject()->getEntity();

    $entity->inPreview = TRUE;
    $store->set($entity->uuid(), $form_state);
    $config = $this->config('bibcite_entity.reference.settings');

    $route_parameters = [
      'bibcite_reference_preview' => $entity->uuid(),
      'view_mode_id' => $config->get('display_override.reference_page_view_mode'),
    ];

    $options = [];
    $query = \Drupal::requestStack()->getCurrentRequest()->query;
    if ($query->has('destination')) {
      $options['query']['destination'] = $query->get('destination');
      $query->remove('destination');
    }
    $form_state->setRedirect('entity.bibcite_reference.preview', $route_parameters, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Reference.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Reference.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.bibcite_reference.canonical', ['bibcite_reference' => $entity->id()]);
  }

}
