/*!
 * Hampusn Current Template 
 * 
 * Main plugin script. Prints the templates in a modal at the top of body.
 * 
 * @author Hampus Nordin <hej@hampusnord.in>
 */
(function($, window, document, undefined) {
  // jQuery document ready
  $(function() {
    var hct = window.hampusnCurrentTemplate || false,
        output = '';
    
    // Make sure our js vars are printed (through wp_localize_script())
    // window.hampusnCurrentTemplate will be empty if they aren't.
    if ( ! hct ) {
      return;
    }

    // Check if the current user is an admin (not the same as is_admin())
    // and that the templates should be printed in a modal at the body top.
    if ( hct.isAdmin !== '1' || hct.showModalInBodyTop !== '1' ) {
      return;
    }

    // Make sure there are actually any templates.
    if ( ! $.isArray( hct.templates ) || ! hct.templates.length ) {
      return;
    }

    // Begin html for modal.
    output = '<div id="hampusn-current-template">' + 
               '<a id="hampusn-current-template-close" href="#">&times;</a>' + 
               '<ul class="hampusn-current-template__list">';

    // Loop through all templates and add them to the html output.
    for ( var i = hct.templates.length - 1; i >= 0; i-- ) {
          // The current loop item
      var template = hct.templates[i],
          // Split up the template into each directory.
          // TODO: Does this work on Windows?
          tplParts = template.split( '/' ),
          // Get the filename which should be the last part.
          // Pop shorts the array by reference, so tplParts 
          // does not contain filename any longer.
          filename = tplParts.pop(),
          // Join the remaining parts together again to 
          // form a directory.
          dir = tplParts.join( '/' );

      // The filename should be highlighted and the rest 
      // of the path should just be toned out/regular.
      output += '<li><span>' + dir + '/</span><strong>' + filename + '</strong></li>';
    };
    // Close template list and container div.
    output += '</ul></div>';

    // Add output to top of body.
    $( 'body' ).prepend( output );

    // Add callback for click on the close button/cross.
    $( '#hampusn-current-template-close' ).on( 'click', function(e) {
      $( '#hampusn-current-template' ).remove();
    });
  });
})(jQuery, this, this.document);