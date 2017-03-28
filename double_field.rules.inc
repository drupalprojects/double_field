<?php

/**
 * @file
 * Rules integration for the double field module.
 */

/**
 * Implements hook_rules_data_info().
 */
function double_field_rules_data_info() {
  return array(
    'double_field' => array(
      'label' => t('Double field'),
      'wrap' => TRUE,
      'property info' => double_field_rules_property_info(),
      'ui class' => 'DoubleFiledDataUIDoubleField',
    ),
  );
}

/**
 * Data property info callback.
 */
function double_field_rules_property_info() {
  return array(
    'first' => array(
      'type' => 'text',
      'label' => t('First subfield'),
    ),
    'second' => array(
      'type' => 'text',
      'label' => t('Second subfield'),
    ),
  );
}

/**
 * UI for double field type parameter.
 */
class DoubleFiledDataUIDoubleField extends RulesDataUI implements RulesDataDirectInputFormInterface {

  /**
   * {@inheritdoc}
   */
  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    foreach (double_field_rules_property_info() as $subfield => $info) {
      $form[$name][$subfield] = array(
        '#type' => 'textarea',
        '#title' => $info['label'],
        '#default_value' => $settings['value'][$subfield],
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function render($value) {
    return array(
      'content' => array(
        '#markup' => is_array($value) ? implode(' & ', $value) : $value,
      ),
    );
  }

}
