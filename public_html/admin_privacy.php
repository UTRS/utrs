<?php 

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('template.php');

//Template header()
skinHeader();

?>
<center><b><?php echo SystemMessages::$privpol_admin['UTRSPrivPol'][$lang];?></b></center>
<p><?php echo SystemMessages::$privpol_admin['StepsForPrivacyAdmin'][$lang];?></p>

<h4><?php echo SystemMessages::$privpol_admin['WikimediaLabsDisclaimerTitle'][$lang];?></h4>
<?php echo SystemMessages::$privpol_admin['WikimediaLabsDisclaimer'][$lang];?>


<h4><?php echo SystemMessages::$privpol_admin['WhatCollectTitle'][$lang];?></h4>
<?php echo SystemMessages::$privpol_admin['WhatCollect'][$lang];?>


<h4><?php echo SystemMessages::$privpol_admin['DataStoreTitle'][$lang];?></h4>

<?php echo SystemMessages::$privpol_admin['DataStore'][$lang];?>

<h4><?php echo SystemMessages::$privpol_admin['UserRightsTitle'][$lang];?></h4>
<?php echo SystemMessages::$privpol_admin['UserRights'][$lang];?>

<h4><?php echo SystemMessages::$privpol_admin['ResponsibilityTitle'][$lang];?></h4>
<?php echo SystemMessages::$privpol_admin['Responsibility'][$lang];?>



<?php 

skinFooter();

?>