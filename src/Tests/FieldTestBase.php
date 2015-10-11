<?php

/**
 * @file
 * Contains \Drupal\double_field\Tests\FieldTestBase.
 */

namespace Drupal\double_field\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Tests the creation of text fields.
 */
abstract class FieldTestBase extends WebTestBase {

  /**
   * A user with relevant administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;


  /**
   * A content type id.
   *
   * @var string
   */
  protected $contentTypeId;


  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * A field name used for testing.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * @var
   */
  protected $contentTypeAdminPath;

  /**
   * @var
   */
  protected $fieldAdminPath;

  /**
   * @var
   */
  protected $fieldStorageAdminPath;

  /**
   * @var
   */
  protected $nodeAddPath;

  /**
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $fieldStorage;

  /**
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;


  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['double_field', 'node', 'field_ui', 'dblog', 'datetime_test', 'datetime'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->contentTypeId = $this->drupalCreateContentType(['type' => 'page'])->id();

    $this->adminUser = $this->drupalCreateUser([
      // @TODO: Remove unused permissions.
      'administer content types',
      'administer site configuration',
      'administer node fields',
      'access content overview',
      'administer nodes',
      'administer node form display',
      'edit any ' . $this->contentTypeId . ' content',
      'delete any ' . $this->contentTypeId . ' content',
    ]);
    $this->drupalLogin($this->adminUser);

    $this->fieldName = strtolower($this->randomMachineName());

    $storage_settings['storage'] = [
      'first' => [
        'type' => 'varchar',
        'maxlength' => 50,
        'precision' => 10,
        'scale' => 2,
      ],
      'second' => [
        'type' => 'varchar',
        'maxlength' => 50,
        'precision' => 10,
        'scale' => 2,
      ],
    ];

    $this->fieldStorage = entity_create('field_storage_config', [
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'type' => 'double_field',
      'settings' => $storage_settings,
    ]);
    $this->fieldStorage->save();
    $this->field = entity_create('field_config', [
      'field_storage' => $this->fieldStorage,
      'bundle' => $this->contentTypeId,
      'required' => TRUE,
    ]);
    $this->field->save();
    $this->contentTypeAdminPath = sprintf('admin/structure/types/manage/%s', $this->contentTypeId);
    $this->fieldAdminPath = sprintf('%s/fields/node.%s.%s', $this->contentTypeAdminPath, $this->contentTypeId, $this->fieldName);
    $this->fieldStorageAdminPath = sprintf('%s/storage', $this->fieldAdminPath);
    $this->nodeAddPath = sprintf('node/add/%s', $this->contentTypeId);
  }

  /**
   * Finds Drupal messages on the page.
   */
  protected function getMessages($type) {
    $messages = [];

    $wrapper = $this->cssSelect('.messages--' . $type);
    if (!empty($wrapper[0])) {
      // Multiple messages are rendered with an HTML list.
      if (isset($wrapper[0]->ul)) {
        foreach ($wrapper[0]->ul->li as $li) {
          $messages[] = trim(strip_tags($li->asXML()));
        }
      }
      else {
        unset($wrapper[0]->h2);
        $messages[] = trim(preg_replace('/\s+/', ' ', strip_tags($wrapper[0]->asXML())));
      }
    }

    return $messages;
  }

  /**
   * Passes if a given error message was found on the page.
   */
  protected function assertErrorMessage($message) {
    $messages = $this->getMessages('error');
    $this->assertTrue(in_array($message, $messages), 'Error message was found.');
  }

  /**
   * Passes if a given warning message was found on the page.
   */
  protected function asserWarningMessage($message) {
    $messages = $this->getMessages('warning');
    $this->assertTrue(in_array($message, $messages), 'Warning message was found.');
  }

  /**
   * Passes if a given status message was found on the page.
   */
  protected function assertStatusMessage($message) {
    $messages = $this->getMessages('status');
    $this->assertTrue(in_array($message, $messages), 'Status message was found.');
  }

  /**
   * Passes if no error messages were found on the page.
   */
  protected function assertNoErrorMessages() {
    $messages = $this->getMessages('error');
    $this->assertTrue(count($messages) == 0, 'No error messages were found.');
  }

  /**
   * Passes if all given attributes matches expectations.
   */
  protected function assertAttributes($attributes, $expected_attributes) {
    foreach ($expected_attributes as $expected_attribute => $value) {
      $expression = isset($attributes[$expected_attribute]) && $attributes[$expected_attribute] == $value;
      $this->assertTrue($expression, sprintf('Valid attribute "%s" was found', $expected_attribute));
    }

  }

  /**
   * Deletes all nodes.
   */
  protected function deleteNodes() {
    $nodes = \Drupal\node\Entity\Node::loadMultiple();
    foreach ($nodes as $node) {
      // Node::delete() does not work here as expected by some reasons.
      $this->drupalPostForm(sprintf('node/%d/delete', $node->id()), [], t('Delete'));
    }
  }


}
