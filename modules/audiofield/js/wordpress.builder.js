/**
 * @file
 * Audiofield build WordPress audio player.
 */

(function ($, Drupal) {
  Drupal.behaviors.audiofield = {
    attach: function (context, settings) {
      $.each(drupalSettings.audiofieldwordpress, function (key, setting_entry) {
        $.each(setting_entry.files, function (key2, file_entry) {
          AudioPlayer.setup("/libraries/wordpress-audio/player.swf", {
            width: 400,
            initialvolume: setting_entry.volume,
            transparentpagebg: "yes",
          });
          AudioPlayer.embed("wordpressaudioplayer_" + file_entry.unique_id, {
            soundFile: file_entry.file,
            titles: file_entry.title,
            autostart: "no",
            loop: "no",
            initialvolume: setting_entry.volume,
            checkpolicy: "yes",
            animation: setting_entry.animate,
          });
        });
      });
    }
  };
})(jQuery, Drupal);
