/**
 * @file
 * Audiofield build AudioJs audio player.
 */

(function ($, Drupal) {
  Drupal.behaviors.audiofield = {
    attach: function (context, settings) {
      $.each(drupalSettings.audiofieldaudiojs, function (key, setting_entry) {
        var audios = document.getElementById(setting_entry.element).getElementsByTagName('audio');

        var audio = audiojs.create(audios[0], {
          css: false,
          createPlayer: {
            markup: false,
            playPauseClass: 'play-pauseZ',
            scrubberClass: 'scrubberZ',
            progressClass: 'progressZ',
            loaderClass: 'loadedZ',
            timeClass: 'timeZ',
            durationClass: 'durationZ',
            playedClass: 'playedZ',
            errorMessageClass: 'error-messageZ',
            playingClass: 'playingZ',
            loadingClass: 'loadingZ',
            errorClass: 'errorZ'
          },
          trackEnded: function () {
            var next = $('ol li.playing').next();
            if (!next.length) {
              next = $('ol li').first();
            }
            next.addClass('playing').siblings().removeClass('playing');
            audio.load($('a', next).attr('data-src'));
            audio.play();
          }
        });

        // Load in the first track.
        first = $('#' + setting_entry.element + ' ol a').attr('data-src');
        $('#' + setting_entry.element + ' ol li').first().addClass('playing');
        audio.load(first);

        // Load in a track on click.
        $('#' + setting_entry.element + ' ol li').click(function (e) {
          e.preventDefault();
          $(this).addClass('playing').siblings().removeClass('playing');
          audio.load($('a', this).attr('data-src'));
          audio.play();
        });
      });

    }
  };
})(jQuery, Drupal);
