(function ($, Drupal, window, document) {

  Drupal.behaviors.basic = {
    attach: function (context, settings) {
      $(window).load(function () {

		 $( "#ptabs" ).tabs();

      });

      $(window).resize(function () {
        // Execute code when the window is resized.
      });

      $(window).scroll(function () {
        // Execute code when the window scrolls.
      });

      $(document).ready(function () {
        // Execute code once the DOM is ready.
      });
    }
  };

} (jQuery, Drupal, this, this.document));
