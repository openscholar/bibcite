<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the reference schema handler.
 */
class ReferenceStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name == 'bibcite_reference_revision') {
      switch ($field_name) {
        case 'revision_user':
          $this->addSharedTableFieldForeignKey($storage_definition, $schema, 'users', 'uid');
          break;
      }
    }

    if ($table_name == 'bibcite_reference_field_data') {
      switch ($field_name) {
        case 'changed':
        case 'created':
          // @todo Revisit index definitions:
          //   https://www.drupal.org/node/2015277.
          $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
          break;
      }
    }

    return $schema;
  }

}
