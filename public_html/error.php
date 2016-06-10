<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('dev/template.php');
require_once('src/messages.php');

skinHeader();

if(!isset($_GET['code']) | $_GET['code'] == 404){
?>

<h2>404: Page not found</h2>

<p>We're sorry, but the page you have attempted to reach is not available. If you followed a link 
from within UTRS, or are sure that you spelled the address of the page correctly, it's possible
the page has temporarily been removed for maintenance. Try checking back again later. If this
error should persist, you may wish to contact the development team so they can look into the 
situation. Thank you!</p>

<?php 
} // closes 404
else if($_GET['code'] == 409){
?>

<h2>409: Conflict encountered</h2>

<p>We're sorry, but the action you have attempted cannot be completed at this time due to a 
conflict within the server. This is often caused by two people attempting the same action
simultaneously, similar to an edit conflict on Wikipedia. You may be able to click your
browser's BACK button and repeat the action, or you may find it has already been completed.
If this error should persist, you may wish to contact the development team so they can look 
into the situation. Thank you!</p>

<?php 
} // closes 409
else if($_GET['code'] == 413 | $_GET['code'] == 431){

if($_GET['code'] == 413){?>
<h2>413: Request too large</h2>
<?php }else{ ?>
<h2>431: Request header too large</h2>
<?php } ?>

<p>We're sorry, but the action you have requested cannot be performed due to an excessive
amount of data. Please press your browser's back button and attempt to reduce the amount of
text you have included in your comments. If this error should persist, you may wish to contact 
the development team so they can look into the situation. Thank you!</p>

<?php 
} // closes 413/431
else if($_GET['code'] == 418){
?>

<h2>418: I'm a teapot</h2>

<p>We're sorry, but the action you have attempted cannot be performed because the server
is a teapot, and by definition can only brew tea. If you insist upon having coffee, then
the server would strongly recommend you purchase a coffee pot and make the vile stuff
yourself. Failing that, go spend half your wallet on an unpronouncable drink at the 
nearest Starbucks. On the other hand, if you press your browser's BACK button, and change
your request to specify tea, the server will most happily serve you a hot cup of Earl Grey
with lemon and crumpets on the side. If this error should persist, you may wish to
consider giving up coffee, as it's clearly becoming somewhat addictive to you, and tea is
a far more civilized drink in any event. Thank you!</p>

<?php 
} // closes 418
else if($_GET['code'] == 500){
?>

<h2>500: Internal server error</h2>

<p>We're sorry, but the server appears to be experiencing some difficulty at this time,
and so is unable to process your request. You may be able to try again later. If this 
error should persist, you may wish to contact the development team or the Toolserver
administrators so they can look into the situation. Thank you!</p>

<?php 
} // closes 500
else if($_GET['code'] == 503){
?>

<h2>503: Service unavailable</h2>

<p>We're sorry, but the Unblock Ticket Request System is temporarily offline for 
maintenance or updates. We hope to be back online soon. In the meantime, if you are 
blocked from editing on Wikipedia, you may wish to consider filing an appeal on your 
user talk page (which you may reach by clicking <a href="http://en.wikipedia.org/wiki/Special:Mytalk">here</a>)
by adding the text <tt>{{unblock|your reason here}}</tt> to the bottom of the page. 
If this error should persist, you may wish to contact the development team so they 
can look into the situation. Thank you!</p>

<?php 
} // closes 503

skinFooter();

?>