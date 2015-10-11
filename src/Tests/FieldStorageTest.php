<?php

/**
 * @file
 * Contains \Drupal\double_field\Tests\FieldStorageTest.
 */

namespace Drupal\double_field\Tests;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Tests the creation of text fields.
 *
 * @group double_field
 */
class FieldStorageTest extends DoubleFieldTestBase {

  /**
   * Passes if all expected violations were found.
   *
   * @param ConstraintViolationList $violations
   *   List of violations to check.
   * @param array $expected_messages
   *   Expected violations messages.
   */
  protected function assertViolations(ConstraintViolationList $violations, array $expected_messages) {
    if (count($violations) == count($expected_messages)) {
      foreach ($violations as $index => $violation) {
        $message = strip_tags($violations[$index]->getMessage());
        $this->assertTrue($message == $expected_messages[$index], 'Violation found: ' . $expected_messages[$index]);
      }
    }
    elseif (count($violations) > count($expected_messages)) {
      $this->error('Unexpected violations were found');
    }
    else {
      $this->error('Not all violations were found');
    }

  }

  /**
   * Test field storage settings.
   */
  function _testFieldStorageSettings() {

    $settings = $this->fieldStorage->getSettings();
    $maxlength = $settings['storage']['second']['maxlength'];

    // -- Boolean and varchar.
    $settings['storage']['first']['type'] = 'boolean';
    $settings['storage']['second']['type'] = 'varchar';
    $this->fieldStorage->setSettings($settings);
    $this->fieldStorage->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => 123,
      'second' => $this->randomString($maxlength + 1)
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('The value you selected is not a valid choice.'),
      t('This value is too long. It should have @maxlength characters or less.', ['@maxlength' => $maxlength]),
    ];
    $this->assertViolations($violations, $expected_messages);

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => mt_rand(0, 1),
      'second' => $this->randomString($maxlength)
    ];

    $violations = $node->{$this->fieldName}->validate();
    $this->assertViolations($violations, []);

    // -- Text (long) and integer.
    $storage_settings['storage']['first']['type'] = 'text';
    $storage_settings['storage']['second']['type'] = 'int';
    $this->fieldStorage->setSettings($storage_settings);
    $this->fieldStorage->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => $this->randomString(1000),
      'second' => 'abc',
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('This value should be of the correct primitive type.'),
    ];
    $this->assertViolations($violations, $expected_messages);

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => $this->randomString(1000),
      'second' => mt_rand(0, 1000)
    ];
    $violations = $node->{$this->fieldName}->validate();
    $this->assertViolations($violations, []);

    // -- Float and numeric.
    $storage_settings['storage']['first']['type'] = 'float';
    $storage_settings['storage']['second']['type'] = 'numeric';
    $this->fieldStorage->setSettings($storage_settings);
    $this->fieldStorage->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => 'abc',
      'second' => 'abc',
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('This value should be of the correct primitive type.'),
      t('This value should be of the correct primitive type.'),
    ];
    $this->assertViolations($violations, $expected_messages);

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => mt_rand(0, 1000) + mt_rand(),
      'second' => mt_rand(0, 1000) + mt_rand(),
    ];
    $violations = $node->{$this->fieldName}->validate();
    $this->assertViolations($violations, []);

  }

  /**
   * Test field storage settings.
   */
  function testFieldStorageSettingsForm() {
    $this->drupalGet($this->fieldStorageAdminPath);

    $expected_options = [
      'boolean',
      'varchar',
      'text',
      'int',
      'float',
      'numeric',
    ];

    $expected_maxlength_attributes = [
      'type' => 'number',
      'value' => 50,
      'step' => 1,
      'min' => 1,
      'required' => 'required',
    ];

    $expected_precision_attributes = [
      'type' => 'number',
      'value' => 10,
      'step' => 1,
      'min' => 10,
      'max' => 32,
      'required' => 'required',
    ];

    $expected_scale_attributes = [
      'type' => 'number',
      'value' => 2,
      'step' => 1,
      'min' => 0,
      'max' => 10,
      'required' => 'required',
    ];

    foreach (['first', 'second'] as $subfield) {
      $select = $this->xpath('//select[@name="settings[storage][' . $subfield . '][type]"]')[0];
      $options = $this->getAllOptions($select);
      foreach ($options as $index => $option) {
        $this->assertTrue($expected_options[$index] == $option->attributes()['value'], 'Option found');
      }

      $maxlength_states['visible'] = [":input[name='settings[storage][$subfield][type]']" => ['value' => 'varchar']];
      $expected_maxlength_attributes['data-drupal-states'] = json_encode($maxlength_states, JSON_HEX_APOS);
      $maxlength_field = $this->xpath(sprintf('//input[@name="settings[storage][%s][maxlength]"]', $subfield))[0];
      $this->assertAttributes($maxlength_field->attributes(), $expected_maxlength_attributes);

      $precision_states['visible'] = [":input[name='settings[storage][$subfield][type]']" => ['value' => 'numeric']];
      $expected_precision_attributes['data-drupal-states'] = json_encode($precision_states, JSON_HEX_APOS);
      $precision_field = $this->xpath(sprintf('//input[@name="settings[storage][%s][precision]"]', $subfield))[0];
      $this->assertAttributes($precision_field->attributes(), $expected_precision_attributes);

      $scale_states['visible'] = [":input[name='settings[storage][$subfield][type]']" => ['value' => 'numeric']];
      $expected_scale_attributes['data-drupal-states'] = json_encode($precision_states, JSON_HEX_APOS);
      $scale_field = $this->xpath(sprintf('//input[@name="settings[storage][%s][scale]"]', $subfield))[0];
      $this->assertAttributes($scale_field->attributes(), $expected_scale_attributes);
    }

    // Submit some example settings and check they are accepted.
    $edit = [
      'settings[storage][first][type]' => 'varchar',
      'settings[storage][first][maxlength]' => 15,
      'settings[storage][second][type]' => 'numeric',
      'settings[storage][second][precision]' => 30,
      'settings[storage][second][scale]' => 5,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));

    $this->assertStatusMessage(sprintf('Updated field %s field settings.', $this->fieldName));

    $this->drupalGet($this->fieldStorageAdminPath);

    $first_select = $this->xpath('//select[@name="settings[storage][first][type]"]')[0];
    $this->assertTrue($this->getSelectedItem($first_select)[0] == 'varchar', 'First selected type is varchar');

    $first_maxlength =$this->xpath('//input[@name="settings[storage][first][maxlength]"]')[0];
    $this->assertTrue($first_maxlength->attributes()['value'] == 15, 'First maxlength value is valid.');

    $second_select = $this->xpath('//select[@name="settings[storage][second][type]"]')[0];
    $this->assertTrue($this->getSelectedItem($second_select)[0] == 'numeric', 'Second selected type is numeric');

    $second_precision =$this->xpath('//input[@name="settings[storage][second][precision]"]')[0];
    $this->assertTrue($second_precision->attributes()['value'] == 30, 'Second precision value is valid.');

    $second_scale =$this->xpath('//input[@name="settings[storage][second][scale]"]')[0];
    $this->assertTrue($second_scale->attributes()['value'] == 5, 'Second scale value is valid.');
  }

}
