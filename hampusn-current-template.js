(function($, w, d, undefined) {
  // jQuery document ready
  $(function() {
    var hct = w.hampusnCurrentTemplate || false,
        output = '';
    // Make sure our js vars are printed (through wp_localize_script())...
    // and that the current user is an admin (not the same as is_admin())...
    // and that the templates should be printed in a modal at the body top...
    // and that there actually are any templates.
    // 
    // If not, exit.
    if (! hct || hct.isAdmin !== '1' || hct.showModalInBodyTop !== '1' || ! $.isArray(hct.templates) || ! hct.templates.length) {
      return;
    }
    // Begin html for modal.
    output = '<div id="hampusn-current-template"><a id="hampusn-current-template-close" href="#">&times;</a><ul class="hampusn-current-template__list">';
    // Loop through all templates and add them to the html output.
    for (var i = hct.templates.length - 1; i >= 0; i--) {
      var tplParts = hct.templates[i].split('/'),
          filename = tplParts.pop();
      // The filename should be highlighted and the rest of the path should just be toned out/regular.
      output += '<li><span>' + tplParts.join('/') + '/</span><strong>' + filename + '</strong></li>';
    };
    output += '</ul></div>';
    // Add output to top of body.
    $('body').prepend(output);
    // Add callback for click on the close button/cross.
    $('#hampusn-current-template-close').on('click', function(e) {
      $('#hampusn-current-template').remove();
    });
  });
})(jQuery, this, this.document);