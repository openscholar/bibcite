<?php

namespace Drupal\sc_pub\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Contributor entity.
 *
 * @ingroup sc_pub
 *
 * @ContentEntityType(
 *   id = "sc_pub_contributor",
 *   label = @Translation("Contributor"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\sc_pub\ContributorListBuilder",
 *
 *     "form" = {
 *       "default" = "Drupal\sc_pub\Form\ContributorForm",
 *       "add" = "Drupal\sc_pub\Form\ContributorForm",
 *       "edit" = "Drupal\sc_pub\Form\ContributorForm",
 *       "delete" = "Drupal\sc_pub\Form\ContributorDeleteForm",
 *     },
 *     "access" = "Drupal\sc_pub\ContributorAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\sc_pub\ContributorHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "sc_pub_contributor",
 *   admin_permission = "administer contributor entities",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/sc_pub_contributor/{sc_pub_contributor}",
 *     "add-form" = "/admin/structure/sc_pub_contributor/add",
 *     "edit-form" = "/admin/structure/sc_pub_contributor/{sc_pub_contributor}/edit",
 *     "delete-form" = "/admin/structure/sc_pub_contributor/{sc_pub_contributor}/delete",
 *     "collection" = "/admin/structure/sc_pub_contributor",
 *   },
 * )
 */
class Contributor extends ContentEntityBase implements ContributorInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $this->generateName();

    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstName() {
    return $this->get('first_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastName() {
    return $this->get('last_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuffix() {
    return $this->get('suffix')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPostfix() {
    return $this->get('postfix')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFirstName($first_name) {
    $this->set('first_name', $first_name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastName($last_name) {
    $this->set('last_name', $last_name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSuffix($suffix) {
    $this->set('suffix', $suffix);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPostfix($postfix) {
    $this->set('postfix', $postfix);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('Can be automatically created from another fields.'))
      ->setDefaultValue('');

    $fields['suffix'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Suffix'))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 1,
      ));

    $fields['first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First name'))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 2,
      ));

    $fields['last_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last name'))
      ->setDefaultValue('')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 3,
      ));

    $fields['postfix'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Postfix'))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 4,
      ));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Generate name from another fields.
   */
  protected function generateName() {
    $last_name = $this->getLastName();
    $first_name = $this->getFirstName();
    $suffix = $this->getSuffix();
    $prefix = $this->getPostfix();

    $name = $last_name;

    if ($prefix && $last_name && $first_name && $suffix) {
      $name = vsprintf('%s %s %s %s', [
        $prefix, $last_name, $first_name, $suffix,
      ]);
    }
    elseif ($prefix && $last_name && $first_name) {
      $name = vsprintf('%s %s %s', [
        $prefix, $last_name, $first_name,
      ]);
    }
    elseif ($last_name && $first_name && $suffix) {
      $name = vsprintf('%s %s %s', [
        $last_name, $first_name, $suffix,
      ]);
    }
    elseif ($prefix && $last_name) {
      $name = vsprintf('%s %s', [
        $prefix, $last_name,
      ]);
    }
    elseif ($last_name && $suffix) {
      $name = vsprintf('%s %s', [
        $last_name, $suffix,
      ]);
    }
    elseif ($last_name && $first_name) {
      $name = vsprintf('%s %s', [
        $last_name, $first_name,
      ]);
    }

    $this->set('name', $name);
  }

}
