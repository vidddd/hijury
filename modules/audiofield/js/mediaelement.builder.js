/**
 * @file
 * Audiofield build MediaElement audio player.
 */

(function ($, Drupal) {
  Drupal.behaviors.audiofield = {
    attach: function (context, settings) {
      $.each(drupalSettings.audiofieldmediaelement, function (key, setting_entry) {
        $.each(setting_entry.elements, function (key, file_entry) {
          $(file_entry).mediaelementplayer({
            startVolume: setting_entry.volume,
            loop: false,
            enableAutosize: true,
            isVideo: false,
          });
        });
      });
    }
  };
})(jQuery, Drupal);
