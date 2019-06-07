<?php

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchemaConverter;

/**
 * Update reference to be revisionable.
 */
function bibcite_entity_post_update_8013(&$sandbox) {
  $entity_type_id ='bibcite_reference';
  $database = \Drupal::database();
  $type_manager = \Drupal::entityTypeManager();

  if (!isset($sandbox['converted'])) {
    $schema_converter = new SqlContentEntityStorageSchemaConverter(
      $entity_type_id,
      $type_manager,
      \Drupal::entityDefinitionUpdateManager(),
      \Drupal::service('entity.last_installed_schema.repository'),
      \Drupal::keyValue('entity.storage_schema.sql'),
      $database
    );

    $schema_converter->convertToRevisionable($sandbox, [
      'uid',
      'title',
      'created',
      'changed',
      'keywords',
      'author',
      'status',
      'bibcite_abst_e',
      'bibcite_abst_f',
      'bibcite_notes',
      'bibcite_custom1',
      'bibcite_custom2',
      'bibcite_custom3',
      'bibcite_custom4',
      'bibcite_custom5',
      'bibcite_custom6',
      'bibcite_custom7',
      'bibcite_auth_address',
      'bibcite_year',
      'bibcite_secondary_title',
      'bibcite_volume',
      'bibcite_edition',
      'bibcite_section',
      'bibcite_issue',
      'bibcite_number_of_volumes',
      'bibcite_number',
      'bibcite_pages',
      'bibcite_date',
      'bibcite_type_of_work',
      'bibcite_lang',
      'bibcite_reprint_edition',
      'bibcite_publisher',
      'bibcite_place_published',
      'bibcite_issn',
      'bibcite_isbn',
      'bibcite_accession_number',
      'bibcite_call_number',
      'bibcite_other_number',
      'bibcite_citekey',
      'bibcite_url',
      'bibcite_doi',
      'bibcite_research_notes',
      'bibcite_tertiary_title',
      'bibcite_short_title',
      'bibcite_alternate_title',
      'bibcite_translated_title',
      'bibcite_original_publication',
      'bibcite_other_author_affiliations',
      'bibcite_remote_db_name',
      'bibcite_remote_db_provider',
      'bibcite_label',
      'bibcite_access_date',
      'bibcite_refereed',
      'bibcite_pmid',
    ]);
    if ($sandbox['#finished'] === 1) {
      $sandbox['converted'] = TRUE;
      $sandbox['#finished'] = 0;
    }
  }

  if (isset($sandbox['converted'])) {
    $table_name = $type_manager->getStorage($entity_type_id)->getEntityType()->getRevisionTable();
    if (!isset($sandbox['total'])) {
      $sandbox['processed'] = 0;
      $sandbox['current_id'] = 0;
      $count_query = $database->select($table_name)->countQuery();
      $sandbox['total'] = $count_query->execute()->fetchField();
    }

    $select_query = $database->select($table_name, 'brr');
    $select_query->fields('brr', ['id', 'created', 'uid'])
      ->condition('id', $sandbox['current_id'], '>');
    $select_query->orderBy('id');
    $select_query->range(0, 50);
    $rows = $select_query->execute()->fetchAll();

    if (count($rows)) {
      foreach ($rows as $row) {
        $database->update($table_name)
          ->fields(['revision_created' => $row->created, 'revision_user' => $row->uid])
          ->condition('id', $row->id)
          ->execute();
        $sandbox['current_id'] = $row->id;
      }
      $sandbox['processed'] += count($rows);
      $sandbox['#finished'] = $sandbox['processed'] / $sandbox['total'];
    }
    else {
      $sandbox['#finished'] = 1;
    }
  }
}
