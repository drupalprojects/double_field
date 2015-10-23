<?php

/**
 * @file
 * Contains \Drupal\double_field\Tests\FormatterTest.
 */

namespace Drupal\double_field\Tests;

/**
 * Tests double field formatters.
 *
 * @group double_field
 */
class FormatterTest extends TestBase {

  /**
   * Test formatter output.
   */
  public function testFormatterOutput() {

    $this->fieldStorage->setCardinality(self::CARDINALITY);
    $this->fieldStorage->save();

    // Create a node for testing.
    $edit = ['title[0][value]' => $this->randomMachineName()];
    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      foreach (['first', 'second'] as $subfield) {
        $edit[$this->fieldName . "[$delta][$subfield]"] = $this->values[$delta][$subfield];
      }
    }
    $this->drupalPostForm($this->nodeAddPath, $edit, t('Save and publish'));

    foreach (['first', 'second'] as $subfield) {
      $settings[$subfield] = [
        'prefix' => $this->randomMachineName(),
        'suffix' => $this->randomMachineName(),
      ];
    }

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $node_path = 'node/' . $node->id();

    // -- Accordion.
    $this->saveFormatterSettings('accordion', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//div[@class='double-field-accordion']/h3)[$index]/a",
        "(//div[@class='double-field-accordion']/div)[$index]",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    // -- Tabs.
    $this->saveFormatterSettings('tabs', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//div[@class='double-field-tabs']/ul/li)[$index]/a[@href='#double-field-tab-$delta']",
        "(//div[@class='double-field-tabs']/div[@id='double-field-tab-$delta'])",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    // -- Table.
    $settings['number_column'] = TRUE;
    $settings['number_column_label'] = $this->randomMachineName();
    $settings['first_column_label'] = $this->randomMachineName();
    $settings['second_column_label'] = $this->randomMachineName();
    $this->saveFormatterSettings('table', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//table[contains(@class, 'double-field-table')]/tbody/tr)[$index]/td[2]",
        "(//table[contains(@class, 'double-field-table')]/tbody/tr)[$index]/td[3]",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    $table_header = $this->xpath('//table[contains(@class, "double-field-table")]/thead/tr');
    $this->assertEqual($settings['number_column_label'], (string) $table_header[0]->th[0], 'Number column label was found.');
    $this->assertEqual($settings['first_column_label'], (string) $table_header[0]->th[1], 'First column label was found.');
    $this->assertEqual($settings['second_column_label'], (string) $table_header[0]->th[2], 'Second column label was found.');

    // Make sure table header disappears if labels are not specified.
    $settings['first_column_label'] = '';
    $settings['second_column_label'] = '';
    $this->saveFormatterSettings('table', $settings);
    $this->drupalGet($node_path);

    $table_header = $this->xpath('//table[contains(@class, "double-field-table")]/thead');
    $this->assertFalse(isset($table_header[0]), 'Table header is not shown.');

    // Test 'hidden' option.
    $settings['first']['hidden'] = TRUE;
    $this->saveFormatterSettings('table', $settings);
    $this->drupalGet($node_path);

    $element = $this->xpath('(//table[contains(@class, "double-field-table")]/tbody/tr)[1]/td[2]/text()');
    $this->assertFalse(isset($element[0]), 'First item was not found.');

    $element = $this->xpath('(//table[contains(@class, "double-field-table")]/tbody/tr)[1]/td[3]/text()');
    $this->assertTrue(isset($element[0]), 'Second item was found.');
    $settings['first']['hidden'] = FALSE;

    // -- Details.
    $this->saveFormatterSettings('details', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      // Details prefix and suffix are not wrapped. So we will check them
      // individually.
      $summary = $this->xpath("(//details[contains(@class, 'double-field-details') and @open])[$index]/summary");
      $first_value = $settings['first']['prefix'] . $this->values[$delta]['first'] . $settings['first']['suffix'];
      $this->assertEqual((string) $summary[0], $first_value, 'Valid summary was found.');

      $details_wrapper = $this->xpath("(//details[contains(@class, 'double-field-details')])[$index]/div[@class='details-wrapper']");
      $second_value = $settings['second']['prefix'] . $this->values[$delta]['second'] . $settings['second']['suffix'];
      $this->assertEqual(trim($details_wrapper[0]), $second_value, 'Valid details content was found.');
    }

    $settings['open'] = FALSE;
    $this->saveFormatterSettings('details', $settings);
    $this->drupalGet($node_path);
    $summary = $this->xpath("//details[contains(@class, 'double-field-details')]")[0];

    $this->assertFalse(isset($summary->attributes()['open']), 'Details element is not open.');

    // -- HTML list.
    $this->saveFormatterSettings('html_list', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//ul[@class='double-field-list']/li[@class='container-inline']/div[@class='double-field-first'])[$index]",
        "(//ul[@class='double-field-list']/li[@class='container-inline']/div[@class='double-field-second'])[$index]",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    $settings['list_type'] = 'ol';
    $this->saveFormatterSettings('html_list', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//ol[@class='double-field-list']/li[@class='container-inline']/div[@class='double-field-first'])[$index]",
        "(//ol[@class='double-field-list']/li[@class='container-inline']/div[@class='double-field-second'])[$index]",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    // Disable 'inline' option and check if the "container-inline" class has
    // been removed.
    $settings['style'] = 'block';
    $this->saveFormatterSettings('html_list', $settings);
    $this->drupalGet($node_path);

    $li_element = $this->xpath('//ol[@class="double-field-list"]/li')[0];
    $this->assertFalse(isset($li_element->attributes()['class']), '"container-inline" class is not found.');

    // -- Definition list.
    $settings['list_type'] = 'dl';
    $this->saveFormatterSettings('html_list', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//dl[@class='double-field-definition-list']/dt)[$index]",
        "(//dl[@class='double-field-definition-list']/dd)[$index]",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    // -- Unformatted list.
    $settings['style'] = 'inline';
    $this->saveFormatterSettings('unformatted_list', $settings);
    $this->drupalGet($node_path);

    for ($delta = 0; $delta < self::CARDINALITY; $delta++) {
      $index = $delta + 1;
      $axes = [
        "(//div[contains(@class, 'double-field-unformatted-list')]/div[contains(@class, 'container-inline')])[$index]/div[@class='double-field-first']",
        "(//div[contains(@class, 'double-field-unformatted-list')]/div[contains(@class, 'container-inline')])[$index]/div[@class='double-field-second']",
      ];
      $this->assertFieldValues($axes, $delta);
    }

    // Disable 'inline' option and check if the "container-inline" class has
    // been removed.
    $settings['style'] = 'block';
    $this->saveFormatterSettings('unformatted_list', $settings);
    $this->drupalGet($node_path);

    $element = $this->xpath('//div[contains(@class, "double-field-unformatted-list")]/div[contains(@class, "container-inline")]');
    $this->assertEqual(count($element), 0, '"container-inline" class is not found.');

    // Test 'hidden' option.
    $settings['first']['hidden'] = TRUE;
    $this->saveFormatterSettings('unformatted_list', $settings);
    $this->drupalGet($node_path);

    $element = $this->xpath('(//div[contains(@class, "double-field-unformatted-list")]/div)[1]/div[@class="double-field-first"]');
    $this->assertFalse(isset($element[0]), 'First item was not found.');

    $element = $this->xpath('(//div[contains(@class, "double-field-unformatted-list")]/div)[1]/div[@class="double-field-second"]');
    $this->assertTrue(isset($element[0]), 'Second item was found.');
  }

  /**
   * Test output of boolean field.
   */
  public function testBooleanLabels() {

  }


  /**
   * Test output list labels.
   */
  public function testListLabels() {

  }

  /**
   * Test formatter settings form.
   */
  public function testFormatterSettingsForm() {

  }


  /**
   * Passes if expected field values were found on the page.
   */
  protected function assertFieldValues($axes, $delta = 0) {
    $settings = $this->getFormatterOptions()['settings'];

    foreach (['first', 'second'] as $index => $subfield) {

      $elements = $this->xpath($axes[$index]);
      if (count($elements) == 0) {
        $this->error(t('Xpath was not found: @xpath', ['@xpath' => $axes[$index]]));
      }
      else {
        $this->assertTrue(trim($elements[0]) == $this->values[$delta][$subfield], 'Valid value was found.');
        $this->assertTrue((string) $elements[0]->span[0] == $settings[$subfield]['prefix'], 'Prefix was found.');
        $this->assertTrue((string) $elements[0]->span[1] == $settings[$subfield]['suffix'], 'Sufix was found.');
      }

    }
  }

}
