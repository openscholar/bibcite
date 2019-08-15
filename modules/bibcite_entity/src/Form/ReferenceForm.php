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
