<?php

/**
 * @file
 * Contains \Drupal\double_field\Tests\WidgetTest.
 */

namespace Drupal\double_field\Tests;

/**
 * Tests double field widget.
 *
 * @group double_field
 */
class WidgetTest extends FieldTestBase {

  /**
   * Test widget form.
   */
  public function testWidgetForm() {

    // -- Boolean and varchar.
    $storage_settings['storage']['first']['type'] = 'boolean';
    $storage_settings['storage']['second']['type'] = 'varchar';
    $this->saveFieldStorageSettings($storage_settings);

    $widget_settings['first']['type'] = 'checkbox';
    $widget_settings['first']['checkbox']['label'] = $this->randomMachineName();
    $widget_settings['second']['type'] = 'textfield';
    $widget_settings['second']['textfield']['size'] = mt_rand(5, 30);
    $widget_settings['second']['textfield']['placeholder'] = $this->randomMachineName();
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->nodeAddPath);

    $this->assertFieldByXPath("//input[@type='checkbox' and @name='{$this->fieldName}[0][first]']", NULL, 'Checkbox found.');
    $label = (string) $this->xpath("//label[@for='edit-{$this->fieldName}-0-first']")[0];
    $this->assertTrue($label == $widget_settings['first']['checkbox']['label'], 'Checkbox label is correct.');

    $textfield = $this->xpath("//input[@name='{$this->fieldName}[0][second]']")[0];
    $expected_attributes = [
      'type' => 'text',
      'value' => '',
      'size' => $widget_settings['second']['textfield']['size'],
      'placeholder' => $widget_settings['second']['textfield']['placeholder'],
    ];
    $this->assertAttributes($textfield->attributes(), $expected_attributes);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => 1,
      $this->fieldName . '[0][second]' => $this->randomMachineName(),
    ];

    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));

    $this->assertFieldValues('On', $edit[$this->fieldName . '[0][second]']);

    $this->deleteNodes();

    // -- Text and integer.
    $storage_settings['storage']['first']['type'] = 'text';
    $storage_settings['storage']['second']['type'] = 'int';
    $this->saveFieldStorageSettings($storage_settings);

    $instance_settings['second']['min'] = mt_rand(-100, 0);
    $instance_settings['second']['max'] = mt_rand(1, 100);
    $this->saveFieldSettings($instance_settings);

    $this->drupalGet($this->fieldAdminPath);

    $widget_settings['first']['type'] = 'textarea';
    $widget_settings['first']['textarea']['cols'] = mt_rand(3, 50);
    $widget_settings['first']['textarea']['rows'] = mt_rand(3, 50);
    $widget_settings['first']['textarea']['placeholder'] = $this->randomMachineName();
    $widget_settings['second']['type'] = 'number';
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->nodeAddPath);

    $textarea = $this->xpath("//textarea[@name='{$this->fieldName}[0][first]']")[0];
    $expected_attributes = [
      'cols' => $widget_settings['first']['textarea']['cols'],
      'rows' => $widget_settings['first']['textarea']['rows'],
      'placeholder' => $widget_settings['first']['textarea']['placeholder'],
    ];
    $this->assertAttributes($textarea->attributes(), $expected_attributes);

    $number_field = $this->xpath("//input[@name='{$this->fieldName}[0][second]']")[0];
    $expected_attributes = [
      'type' => 'number',
      'min' => $instance_settings['second']['min'],
      'max' => $instance_settings['second']['max'],
    ];
    $this->assertAttributes($number_field->attributes(), $expected_attributes);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => $this->randomMachineName(50),
      $this->fieldName . '[0][second]' => mt_rand($instance_settings['second']['min'], $instance_settings['second']['max']),
    ];

    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $this->assertFieldValues($edit[$this->fieldName . '[0][first]'], $edit[$this->fieldName . '[0][second]']);

    $this->deleteNodes();

    // -- Float and numeric.
    $storage_settings['storage']['first']['type'] = 'float';
    $storage_settings['storage']['second']['type'] = 'numeric';
    $this->saveFieldStorageSettings($storage_settings);

    $instance_settings['first']['min'] = mt_rand(-100, 0);
    $instance_settings['first']['max'] = mt_rand(1, 100);
    $instance_settings['second']['min'] = mt_rand(-100, 0);
    $instance_settings['second']['max'] = mt_rand(1, 100);
    $this->saveFieldSettings($instance_settings);

    $widget_settings['first']['type'] = 'number';
    $widget_settings['second']['type'] = 'textfield';
    $widget_settings['second']['textfield']['size'] = mt_rand(5, 30);
    $widget_settings['second']['textfield']['placeholder'] = $this->randomMachineName();
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->nodeAddPath);

    $number_field = $this->xpath("//input[@name='{$this->fieldName}[0][first]']")[0];
    $expected_attributes = [
      'type' => 'number',
      'min' => $instance_settings['first']['min'],
      'max' => $instance_settings['first']['max'],
    ];
    $this->assertAttributes($number_field->attributes(), $expected_attributes);

    $textfield = $this->xpath("//input[@name='{$this->fieldName}[0][second]']")[0];
    $expected_attributes = [
      'type' => 'text',
      'size' => $widget_settings['second']['textfield']['size'],
      'placeholder' => $widget_settings['second']['textfield']['placeholder'],
    ];
    $this->assertAttributes($textfield->attributes(), $expected_attributes);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => mt_rand($instance_settings['first']['min'], $instance_settings['first']['max']),
      $this->fieldName . '[0][second]' => mt_rand($instance_settings['second']['min'], $instance_settings['second']['max']),
    ];

    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $this->assertFieldValues($edit[$this->fieldName . '[0][first]'], $edit[$this->fieldName . '[0][second]']);

    // -- Check prefixes and suffixes.
    $widget_settings['first']['prefix'] = $this->randomMachineName();
    $widget_settings['first']['suffix'] = $this->randomMachineName();
    $widget_settings['second']['prefix'] = $this->randomMachineName();
    $widget_settings['second']['suffix'] = $this->randomMachineName();
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->nodeAddPath);

    $widget_wrapper = $this->xpath("//div[@id='edit-{$this->fieldName}-0']")[0];

    $expected_data[] = $widget_settings['first']['prefix'];
    $expected_data[] = $widget_settings['first']['suffix'];
    $expected_data[] = $widget_settings['second']['prefix'];
    $expected_data[] = $widget_settings['second']['suffix'];

    $this->assertTrue(str_replace("\n", '', $widget_wrapper) == implode($expected_data), 'All prefixes and suffixes were found.');
  }

  /**
   * Passes if expected field values were found on the page.
   */
  protected function assertFieldValues($first_value, $second_value) {
    $value = (string) $this->xpath("//div[@class='double-field-first']")[0];
    $this->assertTrue($value == $first_value, 'First value is correct.');
    $value = (string) $this->xpath("//div[@class='double-field-second']")[0];
    $this->assertTrue($value == $second_value, 'Second value is correct.');
  }

}
