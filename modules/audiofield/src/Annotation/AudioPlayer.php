<?php

namespace Drupal\audiofield\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a AudioPlayer annotation object..
 *
 * @Annotation
 */
class AudioPlayer extends Plugin {

  public $id;
  public $title = "";
  public $fileTypes = array();
  public $description = "";

}
