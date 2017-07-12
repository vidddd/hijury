<?php

namespace Drupal\audiofield;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Defines an interface for audio field player renderer.
 */
interface AudioFieldPluginInterface {

  /**
   * Provide a description of the audio player.
   *
   * @return string
   *   A string description of the audio player.
   */
  public function description();

  /**
   * Renders the player.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The uploaded item list.
   * @param string $langcode
   *   The language code.
   * @param array $settings
   *   An array of additional render settings.
   *
   * @return array
   *   Returns the rendered array.
   */
  public function renderPlayer(FieldItemListInterface $items, $langcode, $settings);

  /**
   * Checks to see if this audio plugin has been properly installed.
   *
   * @return bool
   *   Returns a boolean indicating install state.
   */
  public function checkInstalled();

}
