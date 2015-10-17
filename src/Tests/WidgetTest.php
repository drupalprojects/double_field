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
class WidgetTest extends TestBase {

  /**
   * Passes if expected field values were found on the page.
   */
  protected function assertFieldValues($first_value, $second_value) {
    $value = (string) $this->xpath("//div[@class='double-field-first']")[0];
    $this->assertTrue($value == $first_value, 'First value is correct.');
    $value = (string) $this->xpath("//div[@class='double-field-second']")[0];
    $this->assertTrue($value == $second_value, 'Second value is correct.');
  }

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

    $this->assertFieldByXPath("//input[@type='checkbox' and @name='{$this->fieldName}[0][first]']", NULL, 'Checkbox was found.');
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
      $this->fieldName . '[0][first]' => (bool) mt_rand(0, 1),
      $this->fieldName . '[0][second]' => $this->randomMachineName(),
    ];
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));

    $this->assertFieldValues(
      $edit[$this->fieldName . '[0][first]'] ? 'On' : 'Off',
      $edit[$this->fieldName . '[0][second]']
    );
    $this->deleteNodes();

    // -- Text and integer.
    $storage_settings['storage']['first']['type'] = 'text';
    $storage_settings['storage']['second']['type'] = 'int';
    $this->saveFieldStorageSettings($storage_settings);

    $instance_settings['second']['min'] = mt_rand(-1000, 0);
    $instance_settings['second']['max'] = mt_rand(1, 1000);
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

    $instance_settings['first']['min'] = mt_rand(-1000, 0);
    $instance_settings['first']['max'] = mt_rand(1, 1000);
    $instance_settings['second']['min'] = mt_rand(-1000, 0);
    $instance_settings['second']['max'] = mt_rand(1, 1000);
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
   * Test widget settings form.
   */
  public function testWidgetSettingsForm() {

    $name_prefix = "fields[{$this->fieldName}][settings_edit_form][settings]";

    $default_axes = [
      "//input[@name='{$name_prefix}[inline]' and @type='checkbox']",
      "//input[@name='{$name_prefix}[first][prefix]']",
      "//input[@name='{$name_prefix}[first][suffix]']",
      "//input[@name='{$name_prefix}[second][prefix]']",
      "//input[@name='{$name_prefix}[second][suffix]']",
    ];

    // -- Boolean and varchar.
    $storage_settings['storage']['first']['type'] = 'boolean';
    $storage_settings['storage']['second']['type'] = 'varchar';
    $this->saveFieldStorageSettings($storage_settings);

    $widget_settings['first']['type'] = 'boolean';
    $widget_settings['second']['type'] = 'textfield';
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->formDisplayAdminPath);

    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, [], $this->fieldName . '_settings_edit');

    $axes = $default_axes;
    $axes[] = "//select[@name='{$name_prefix}[first][type]']/option[@value='checkbox']";
    $axes[] = "//summary[text()='First subfield - Boolean']";
    $axes[] = "//input[@name='{$name_prefix}[first][checkbox][label]' and @value='Ok']";
    $axes[] = "//select[@name='{$name_prefix}[second][type]']/option[@value='textfield' and @selected]";
    $axes[] = "//summary[text()='Second subfield - Text']";
    $axes[] = "//input[@name='{$name_prefix}[second][textfield][size]']";
    $axes[] = "//input[@name='{$name_prefix}[second][textfield][placeholder]']";
    $this->assertAxes($axes);

    $edit = [
      $name_prefix . '[inline]' => TRUE,
      $name_prefix . '[first][checkbox][label]' => $this->randomMachineName(),
      $name_prefix . '[first][prefix]' => $this->randomMachineName(),
      $name_prefix . '[first][suffix]' => $this->randomMachineName(),
      $name_prefix . '[second][textfield][size]' => mt_rand(1, 10),
      $name_prefix . '[second][textfield][placeholder]' => $this->randomMachineName(),
      $name_prefix . '[second][prefix]' => $this->randomMachineName(),
      $name_prefix . '[second][suffix]' => $this->randomMachineName(),
    ];

    $this->drupalPostAjaxForm(NULL, $edit, $this->fieldName . '_plugin_settings_update');
    $this->drupalPostForm(NULL, [], t('Save'));

    $summary = $this->xpath("//tr[@id='$this->fieldName']//div[@class='field-plugin-summary']")[0]->asXML();

    // Remove wrapper.
    $summary = str_replace(['<div class="field-plugin-summary">', '</div>'], '', $summary);
    $summary_items = explode('<br/>', $summary);
    $expected_summary_items = [
      t('Display as inline element'),
      '<b>First subfield - boolean</b>',
      t('Widget: !first_widget', ['!first_widget' => 'checkbox']),
      t('Label: !label', ['!label' => $edit[$name_prefix . '[first][checkbox][label]']]),
      t('Prefix: !prefix', ['!prefix' => $edit[$name_prefix . '[first][prefix]']]),
      t('Suffix: !suffix', ['!suffix' => $edit[$name_prefix . '[first][suffix]']]),
      '<b>Second subfield - text</b>',
      t('Widget: !widget', ['!widget' => 'textfield']),
      t('Size: !size', ['!size' => $edit[$name_prefix . '[second][textfield][size]']]),
      t('Placeholder: !placeholder', ['!placeholder' => $edit[$name_prefix . '[second][textfield][placeholder]']]),
      t('Prefix: !prefix', ['!prefix' => $edit[$name_prefix . '[second][prefix]']]),
      t('Suffix: !suffix', ['!suffix' => $edit[$name_prefix . '[second][suffix]']]),
    ];

    $this->assertIdenticalArray($summary_items, $expected_summary_items, 'Valid summary was found.');

    // -- Text and integer.
    $storage_settings['storage']['first']['type'] = 'text';
    $storage_settings['storage']['second']['type'] = 'int';
    $this->saveFieldStorageSettings($storage_settings);

    $widget_settings['first']['type'] = 'textarea';
    $widget_settings['second']['type'] = 'number';
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->formDisplayAdminPath);
    $this->drupalPostAjaxForm(NULL, [], $this->fieldName . '_settings_edit');

    $axes = $default_axes;
    $axes[] = "//select[@name='{$name_prefix}[first][type]']/option[@value='textarea' and @selected]";
    $axes[] = "//summary[text()='First subfield - Text (long)']";
    $axes[] = "//input[@name='{$name_prefix}[first][textarea][cols]']";
    $axes[] = "//input[@name='{$name_prefix}[first][textarea][rows]']";
    $axes[] = "//select[@name='{$name_prefix}[second][type]']/option[@value='number' and @selected]";
    $axes[] = "//summary[text()='Second subfield - Integer']";
    $this->assertAxes($axes);

    $edit = [
      $name_prefix . '[inline]' => FALSE,
      $name_prefix . '[first][textarea][cols]' => mt_rand(1, 10),
      $name_prefix . '[first][textarea][rows]' => mt_rand(1, 10),
      $name_prefix . '[first][textarea][placeholder]' => $this->randomMachineName(),
      $name_prefix . '[first][prefix]' => $this->randomMachineName(),
      $name_prefix . '[first][suffix]' => $this->randomMachineName(),
      $name_prefix . '[second][prefix]' => $this->randomMachineName(),
      $name_prefix . '[second][suffix]' => $this->randomMachineName(),
    ];

    $this->drupalPostAjaxForm(NULL, $edit, $this->fieldName . '_plugin_settings_update');
    $this->drupalPostForm(NULL, [], t('Save'));

    $summary = $this->xpath("//tr[@id='$this->fieldName']//div[@class='field-plugin-summary']")[0]->asXML();

    $summary = str_replace(['<div class="field-plugin-summary">', '</div>'], '', $summary);
    $summary_items = explode('<br/>', $summary);
    $expected_summary_items = [
      '<b>First subfield - text (long)</b>',
      t('Widget: !widget', ['!widget' => 'textarea']),
      t('Columns: !cols', ['!cols' => $edit[$name_prefix . '[first][textarea][cols]']]),
      t('Rows: !rows', ['!rows' => $edit[$name_prefix . '[first][textarea][rows]']]),
      t('Placeholder: !placeholder', ['!placeholder' => $edit[$name_prefix . '[first][textarea][placeholder]']]),
      t('Prefix: !prefix', ['!prefix' => $edit[$name_prefix . '[first][prefix]']]),
      t('Suffix: !suffix', ['!suffix' => $edit[$name_prefix . '[first][suffix]']]),
      '<b>Second subfield - integer</b>',
      t('Widget: !widget', ['!widget' => 'number']),
      t('Prefix: !prefix', ['!prefix' => $edit[$name_prefix . '[second][prefix]']]),
      t('Suffix: !suffix', ['!suffix' => $edit[$name_prefix . '[second][suffix]']]),
    ];

    $this->assertIdenticalArray($summary_items, $expected_summary_items, 'Valid summary was found.');

    // -- Float and decimal.
    $storage_settings['storage']['first']['type'] = 'float';
    $storage_settings['storage']['second']['type'] = 'numeric';
    $this->saveFieldStorageSettings($storage_settings);

    $widget_settings['first']['type'] = 'number';
    $widget_settings['second']['type'] = 'textfield';
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->formDisplayAdminPath);

    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, [], $this->fieldName . '_settings_edit');

    $axes = $default_axes;
    $axes[] = "//select[@name='{$name_prefix}[first][type]']/option[@value='number' and @selected]";
    $axes[] = "//summary[text()='First subfield - Float']";
    $axes[] = "//select[@name='{$name_prefix}[second][type]']/option[@value='textfield' and @selected]";
    $axes[] = "//summary[text()='Second subfield - Decimal']";
    $this->assertAxes($axes);
  }

  /**
   * Test validation.
   */
  protected function testValidation() {

    // -- Varchar.
    $maxlength = 10;
    $storage_settings['storage']['first']['type'] = 'varchar';
    $storage_settings['storage']['first']['maxlength'] = $maxlength;
    $storage_settings['storage']['second']['type'] = 'varchar';
    $this->saveFieldStorageSettings($storage_settings);

    $widget_settings['first']['type'] = 'textfield';
    $widget_settings['second']['type'] = 'email';
    $this->saveWidgetSettings($widget_settings);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => $this->randomMachineName($maxlength + 1),
      $this->fieldName . '[0][second]' => 'not@valid@email',
    ];

    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $error_message = t(
      '@field_name cannot be longer than @max_length characters but is currently @actual_length characters long.',
      ['@field_name' => $this->fieldName, '@max_length' => $maxlength, '@actual_length' => strlen($edit[$this->fieldName . '[0][first]'])]
    );
    $this->assertErrorMessage($error_message);
    $this->assertErrorMessage(t('The email address @email is not valid.', ['@email' => 'not@valid@email']));

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => $this->randomMachineName($maxlength),
      $this->fieldName . '[0][second]' => 'test@exampe.com',
    ];
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $this->assertNoErrorMessages();

    $field_settings['first']['list'] = TRUE;
    $field_settings['first']['allowed_values'] = [
      'aaa' => 'Aaa',
      'bbb' => 'Bbb',
      'ccc' => 'Ccc',
    ];
    $this->saveFieldSettings($field_settings);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => 'ddd',
    ];

    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $this->assertErrorMessage(t('The value you selected is not a valid choice.'));
    $this->assertErrorMessage(t('This value should not be blank.'));

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => array_rand($field_settings['first']['allowed_values']),
      $this->fieldName . '[0][second]' => 'test@exampe.com',
    ];
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $this->assertNoErrorMessages();

    $this->deleteNodes();

    // -- Integer and float.
    $storage_settings['storage']['first']['type'] = 'int';
    $storage_settings['storage']['first']['maxlength'] = NULL;
    $storage_settings['storage']['second']['type'] = 'float';
    $this->saveFieldStorageSettings($storage_settings);

    $field_settings['first']['list'] = FALSE;
    $field_settings['first']['min'] = mt_rand(-1000, 1000);
    $field_settings['first']['max'] = $field_settings['first']['min'] + mt_rand(1, 1000);
    $field_settings['second']['min'] = mt_rand(-1000, 1000);
    $field_settings['second']['max'] = $field_settings['second']['min'] + mt_rand(1, 1000);
    $this->saveFieldSettings($field_settings);

    $widget_settings['first']['type'] = 'textfield';
    $widget_settings['second']['type'] = 'number';
    $this->saveWidgetSettings($widget_settings);

    $this->drupalGet($this->nodeAddPath);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' => 100,
      $this->fieldName . '[0][second]' => $field_settings['second']['max'] + 1,
    ];
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $error_message = t(
      '@field_name must be lower than or equal to @max.',
      ['@field_name' => $this->fieldName, '@max' => $field_settings['second']['max']]
    );
    $this->assertErrorMessage($error_message);
    $this->assertEqual(1, count($this->getMessages('error')), 'There should be only one error message');

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      $this->fieldName . '[0][first]' =>  $field_settings['first']['min'] - 1,
      $this->fieldName . '[0][second]' => mt_rand($field_settings['second']['min'], $field_settings['second']['max']),
    ];
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));

    // This error comes from primitive type constraint because, textfield form
    // element does not support min and max properties.
    $error_message = t(
      'This value should be @min or more.',
      ['@min' => $field_settings['first']['min']]
    );
    $this->assertErrorMessage($error_message);
    $this->assertEqual(1, count($this->getMessages('error')), 'There should be only one error message');

    $edit[$this->fieldName . '[0][first]'] = mt_rand($field_settings['first']['min'], $field_settings['first']['max']);
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));
    $this->assertNoErrorMessages();
  }

}
