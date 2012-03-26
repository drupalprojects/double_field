<?php
/**
 * @file
 * Functions needed for devel_generate integration.
 */

/**
 * Implements hook_devel_generate().
 */
function double_field_devel_generate($object, $field, $instance, $bundle) {

  list($widget['first'], $widget['second']) = explode('_&_', $instance['widget']['type']);

  foreach (array('first', 'second') as $subfield) {
    switch ($widget[$subfield]) {
      case 'checkbox':
        $object_field[$subfield] = $instance['widget']['settings'][$subfield]['checkbox'][mt_rand(0, 1) ? 'on_value' : 'off_value'];
        break;

      case 'select':
        if ($allowed_values = array_keys($instance['widget']['settings'][$subfield]['select']['allowed_values'])) {
          $object_field[$subfield] = $allowed_values[mt_rand(0, count($allowed_values) -1)];
        }
        break;

      default:
        $object_field[$subfield] = user_password($field['settings'][$subfield]['maxlength']);
    }
  }

  return $object_field;
}
