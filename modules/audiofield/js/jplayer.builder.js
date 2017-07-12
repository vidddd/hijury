/**
 * @file
 * Audiofield build jPlayer audio players of various types.
 */

(function ($, Drupal) {
  Drupal.behaviors.audiofield = {
    attach: function (context, settings) {
      $.each(drupalSettings.audiofieldjplayer, function (key, setting_entry) {
        // Default audio player.
        if (setting_entry.playertype == 'default') {
          $("#jquery_jplayer_" + setting_entry.unique_id).jPlayer(
          {
            cssSelectorAncestor: "#jp_container_" + setting_entry.unique_id
          },
          {
            ready: function (event) {
              var media_array = {
                title: setting_entry.description,
              };
              media_array[setting_entry.filetype] = setting_entry.file;
              $(this).jPlayer("setMedia", media_array);
            },
            swfPath: "/libraries/jplayer/dist/jplayer",
            supplied: setting_entry.filetype,
            wmode: "window",
            useStateClassSkin: true,
            autoBlur: false,
            smoothPlayBar: true,
            keyEnabled: true,
            remainingDuration: false,
            toggleDuration: false,
            volume: setting_entry.volume,
          });
        }
        // Playlist audio player.
        else if (setting_entry.playertype == 'playlist') {
          var myPlaylist = new jPlayerPlaylist({
              jPlayer: "#jquery_jplayer_" + setting_entry.unique_id,
              cssSelectorAncestor: "#jp_container_" + setting_entry.unique_id
            }, [], {
            playlistOptions: {
              enableRemoveControls: false
            },
            swfPath: "/libraries/jplayer/dist/jplayer",
            wmode: "window",
            useStateClassSkin: true,
            autoBlur: false,
            smoothPlayBar: true,
            keyEnabled: true,
            volume: setting_entry.volume,
          });

          $.each(setting_entry.files, function (key, file_entry) {
            var media_array = {
              title: file_entry.description,
            };
            media_array[file_entry.filetype] = file_entry.file;
            myPlaylist.add(media_array);
          });
        }
        // Circle audio player.
        else if (setting_entry.playertype == 'circle') {
          $.each(setting_entry.files, function (key, file_entry) {
            var media_array = {};
            media_array[file_entry.filetype] = file_entry.file;
            var myCirclePlayer = new CirclePlayer(
              "#jquery_jplayer_" + file_entry.unique_id,
              media_array, {
              cssSelectorAncestor: "#cp_container_" + file_entry.unique_id,
              swfPath: "/libraries/jplayer/dist/jplayer",
              wmode: "window",
              keyEnabled: true,
              supplied: file_entry.filetype,
            });
          });
        }
      });
    }
  };
})(jQuery, Drupal);
