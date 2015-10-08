<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\Accordion.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementations for 'accordion' formatter.
 *
 * @FieldFormatter(
 *  id = "accordion",
 *  label = @Translation("Accordion"),
 *  field_types = {"double_field"}
 * )
 */
class Accordion extends Base {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

	$settings = $this->getSettings();
    $this->prepareItems($items);

	$element[0] = [
	  '#theme' => 'double_field_accordion',
	  '#items' => $items,
	  '#settings' => $settings,
	  '#attached' => ['library' => ['core/jquery.ui.accordion']],
	];

	return $element;

  }

}
