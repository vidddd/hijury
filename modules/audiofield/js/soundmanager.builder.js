/**
 * @file
 * Audiofield build SoundManager audio player.
 */

(function ($, Drupal) {
  Drupal.behaviors.audiofield = {
    attach: function (context, settings) {
      soundManager.setup({
        // Required: path to directory containing SM2 SWF files.
        url: drupalSettings.audiofieldsoundmanager.swfpath,
        preferFlash: false,
        defaultOptions: {
          volume: drupalSettings.audiofieldsoundmanager.volume,
        },
      });
    }
  };
})(jQuery, Drupal);
