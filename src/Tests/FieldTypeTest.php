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
class FieldTypeTest extends FieldTestBase {

  /**
   * Test field storage settings.
   */
  function testFieldStorageSettings() {

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

    // --
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

    // --
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

    // Submit some example settings and check whether they are accepted.
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

  /**
   * Test field settings.
   */
  function testFieldSettings() {

    $storage_settings = $this->fieldStorage->getSettings();

    // -- Boolean and varchar.
    $storage_settings['storage']['first']['type'] = 'boolean';
    $storage_settings['storage']['second']['type'] = 'varchar';
    $this->fieldStorage->setSettings($storage_settings);
    $this->fieldStorage->save();

    $settings = $this->field->getSettings();
    $settings['second']['list'] = TRUE;
    $settings['second']['allowed_values'] = [
      'aaa' => 'Aaa',
      'bbb' => 'Bbb',
      'ccc' => 'Ccc',
    ];
    $this->field->setSettings($settings);
    $this->field->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' =>  NULL,
      'second' => 'abc',
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('This value should not be blank.'),
      t('The value you selected is not a valid choice.'),
    ];
    $this->assertViolations($violations, $expected_messages);

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => 0,
      'second' => array_rand($settings['second']['allowed_values']),
    ];

    $violations = $node->{$this->fieldName}->validate();
    $this->assertViolations($violations, []);

    // -- Integer.
    $storage_settings['storage']['first']['type'] = 'int';
    $storage_settings['storage']['second']['type'] = 'int';
    $this->fieldStorage->setSettings($storage_settings);
    $this->fieldStorage->save();

    $min_limit = mt_rand(-1000, 1000);
    $max_limit = mt_rand($min_limit, $min_limit + 1000);
    $settings = $this->field->getSettings();
    $settings['first']['list'] = FALSE;
    $settings['first']['min'] = $min_limit;
    $settings['first']['max'] = $max_limit;
    $settings['second']['list'] = FALSE;
    $settings['second']['min'] = $min_limit;
    $settings['second']['max'] = $max_limit;
    $this->field->setSettings($settings);
    $this->field->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' =>  mt_rand($min_limit - 1000, $min_limit - 1),
      'second' => mt_rand($max_limit + 1, $max_limit + 1000),
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('This value should be @min_limit or more.', ['@min_limit' => $min_limit]),
      t('This value should be @max_limit or less.', ['@max_limit' => $max_limit]),
    ];
    $this->assertViolations($violations, $expected_messages);

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => mt_rand($min_limit, $max_limit),
      'second' => mt_rand($min_limit, $max_limit),
    ];

    $violations = $node->{$this->fieldName}->validate();
    $this->assertViolations($violations, []);

    // -- Float and numeric.
    // --
    $storage_settings['storage']['first']['type'] = 'float';
    $storage_settings['storage']['second']['type'] = 'numeric';
    $this->fieldStorage->setSettings($storage_settings);
    $this->fieldStorage->save();

    $min_limit = mt_rand(-1000, 1000);
    $max_limit = mt_rand($min_limit, $min_limit + 1000);
    $settings = $this->field->getSettings();
    $settings['first']['list'] = FALSE;
    $settings['first']['min'] = $min_limit;
    $settings['first']['max'] = $max_limit;
    $settings['second']['list'] = FALSE;
    $settings['second']['min'] = $min_limit;
    $settings['second']['max'] = $max_limit;
    $this->field->setSettings($settings);
    $this->field->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' =>  mt_rand($min_limit - 1000, $min_limit - 1),
      'second' => mt_rand($max_limit + 1, $max_limit + 1000),
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('This value should be @min_limit or more.', ['@min_limit' => $min_limit]),
      t('This value should be @max_limit or less.', ['@max_limit' => $max_limit]),
    ];
    $this->assertViolations($violations, $expected_messages);

    // --
/*  @TODO: Fix 'key contains a dot which is not supported' exception.
    $settings['first']['list'] = TRUE;
    $settings['first']['allowed_values'] = [
      '-12.379' => 'Aaa',
      '04565' => 'Bbb',
      '93577285' => 'Ccc',
    ];
    $settings['second']['list'] = TRUE;
    $settings['second']['allowed_values'] = [
      '-245' => 'Aaa',
      '1987' => 'Bbb',
      '7738854' => 'Ccc',
    ];
    $this->field->setSettings($settings);
    $this->field->save();

    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => 123.356,
      'second' => 300.12,
    ];

    $violations = $node->{$this->fieldName}->validate();
    $expected_messages = [
      t('The value you selected is not a valid choice.'),
      t('The value you selected is not a valid choice.'),
    ];
    $this->assertViolations($violations, $expected_messages);

    // --
    $node = Node::create(['type' => $this->contentTypeId]);
    $node->{$this->fieldName} = [
      'first' => 111,
      'second' => 222,
    ];

    $violations = $node->{$this->fieldName}->validate();
    $this->assertViolations($violations, []);

    foreach ($violations as $index => $violation) {
      $message = strip_tags($violations[$index]->getMessage());
      debug($message);
    }
 */

  }

  /**
   * Test field settings form.
   */
  function testFieldSettingsForm() {

    $storage_settings = $this->fieldStorage->getSettings();

    $storage_types = [
      'boolean',
      'varchar',
      'text',
      'int',
      'float',
      'numeric',
    ];

    for ($i = 0, $total_types = count($storage_types); $i < $total_types; $i += 2) {

      $storage_settings['storage']['first']['type'] = $storage_types[$i];
      $storage_settings['storage']['second']['type'] = $storage_types[$i + 1];
      $this->fieldStorage->setSettings($storage_settings);
      $this->fieldStorage->save();
      $this->drupalGet($this->fieldAdminPath);

      foreach (['first', 'second'] as $subfield) {

        $required_field = $this->xpath("//input[@name='settings[$subfield][required]']")[0];
        $this->assertTrue($required_field->attributes()['checked'] == 'checked', 'Subfield is required');

        $summary = $this->xpath("//summary[@aria-controls='edit-settings-$subfield']")[0];
        $summary_type = explode(' - ', $summary)[1];

        switch ($storage_settings['storage'][$subfield]['type']) {

          case 'boolean':
            $this->assertTrue($summary_type  == 'Boolean', 'Summary type is correct');

            $on_label_field = $this->xpath("//input[@name='settings[$subfield][on_label]']")[0];
            $expected_attributes = [
              'type' => 'text',
              'value' => 'On',
            ];
            $this->assertAttributes($on_label_field->attributes(), $expected_attributes);

            $off_label_field = $this->xpath("//input[@name='settings[$subfield][off_label]']")[0];
            $expected_attributes = [
              'type' => 'text',
              'value' => 'Off',
            ];
            $this->assertAttributes($off_label_field->attributes(), $expected_attributes);
            break;

          case 'varchar':
            $this->assertTrue($summary_type == 'Text', 'Summary type is correct');
            $this->assertAllowedValues($subfield);
            break;

          case 'text':
            $this->assertTrue($summary_type  == 'Text (long)', 'Summary type is correct');
            $this->assertNoFieldByXPath("//textarea[@name='settings[$subfield][allowed_values]']", NULL, 'Allowed values field is absent');
            break;

          case 'int':
            $this->assertTrue($summary_type  == 'Integer', 'Summary type is correct');
            $this->assertAllowedValues($subfield);
            $this->assertRangeFields($subfield);
            break;

          case 'float':
            $this->assertTrue($summary_type  == 'Float', 'Summary type is correct');
            $this->assertRangeFields($subfield);
            $this->assertAllowedValues($subfield);
            break;

          case 'numeric':
            $this->assertTrue($summary_type  == 'Decimal', 'Summary type is correct');
            $this->assertRangeFields($subfield);
            $this->assertAllowedValues($subfield);
            break;

        }

      }

    }

    // Submit some example settings and check whether they are accepted.
    $edit = [
      'settings[first][list]' => 1,
      'settings[first][allowed_values]' => '123|Aaa',
      'settings[second][min]' => 10,
      'settings[second][max]' => 20,
    ];

    $this->drupalPostForm(NULL, $edit, t('Save settings'));
    $this->drupalGet($this->fieldAdminPath);

    $first_list_field = $this->xpath('//input[@name="settings[first][list]"]')[0];
    $this->assertTrue($first_list_field->attributes()['checked'] == 'checked', 'First list field is checked');

    $first_allowed_values_field = $this->xpath('//textarea[@name="settings[first][allowed_values]"]')[0];
    $this->assertTrue($first_allowed_values_field == '123|Aaa', 'Found valid allowed values ');

    $first_min_field = $this->xpath('//input[@name="settings[second][min]"]')[0];
    $this->assertTrue($first_min_field->attributes()['value'] == 10, 'Min value is correct');

    $first_max_field = $this->xpath('//input[@name="settings[second][max]"]')[0];
    $this->assertTrue($first_max_field->attributes()['value'] == 20, 'Max value is correct');

  }

  /**
   * Passes if range fields are found for a given subfield.
   */
  protected function assertRangeFields($subfield) {
    $states['visible'][":input[name='settings[$subfield][list]']"]['checked'] = FALSE;

    $min_field = $this->xpath("//input[@name='settings[$subfield][min]']")[0];
    $expected_attributes = [
      'type' => 'number',
      'data-drupal-states' => json_encode($states, JSON_HEX_APOS),
    ];
    $this->assertAttributes($min_field->attributes(), $expected_attributes);

    $max_field = $this->xpath("//input[@name='settings[$subfield][max]']")[0];
    $expected_attributes = [
      'type' => 'number',
      'data-drupal-states' => json_encode($states, JSON_HEX_APOS),
    ];
    $this->assertAttributes($max_field->attributes(), $expected_attributes);
  }

  /**
   * Passes if allowed values textarea is found for a given subfield.
   */
  protected function assertAllowedValues($subfield) {
    $list_field = $this->xpath("//input[@name='settings[$subfield][list]']")[0];
    $this->assertTrue($list_field->attributes()['checked'] == NULL, 'List checkbox is checked');
    $allowed_values_field = $this->xpath("//textarea[@name='settings[$subfield][allowed_values]']")[0];
    $states['invisible'] = [":input[name='settings[$subfield][list]']" => ['checked' => FALSE]];
    $expected_attributes = [
      'rows' => 10,
      'data-drupal-states' => json_encode($states, JSON_HEX_APOS),
    ];
    $this->assertAttributes($allowed_values_field->attributes(), $expected_attributes);
  }

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
      debug(count($violations) . '!=' . count($expected_messages));
      $this->error('Unexpected violations were found');
    }
    else {
      $this->error('Not all violations were found');
    }
  }

}
