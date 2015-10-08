<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\Tabs.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementations for 'tabs' formatter.
 *
 * @FieldFormatter(
 *  id = "tabs",
 *  label = @Translation("Tabs"),
 *  field_types = {"double_field"}
 * )
 */
class Tabs extends Base {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $settings = $this->getSettings();
    $this->prepareItems($items);

    $element[0] = array(
      '#theme' => 'double_field_tabs',
      '#items' => $items,
      '#settings' => $settings,
      '#attached' => ['library' => ['core/jquery.ui.tabs']],
    );

    return $element;

  }

}
