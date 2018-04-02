<?php
require_once('src/unblocklib.php');
require_once('src/noticeObject.php');

function skinHeader($script = '', $adminNav = false, $recaptcha = false) {

$loggedIn = loggedIn();

$sitenoticeText = "";

if($loggedIn){
   try{
      $db = connectToDB();
      $query = $db->query("SELECT message FROM sitenotice ORDER BY messageID ASC");
      if($query === false){
         $error = var_export($db->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }

      while (($message = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
         $sitenoticeText .= "<li>" . Notice::format($message['message']) . "</li>";
      }

      $query->closeCursor();
   }
   catch(UTRSException $e){
      $sitenoticeText = "<li>An error occured when getting the sitenotice: " . $e->getMessage() . "</li>\n";
   }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<link rel="stylesheet" href="unblock_styles.css?<?php /* Forces browsers to re-fetch the stylesheet when it changes */ echo sha1(file_get_contents('unblock_styles.css')) ?>">
<title>Unblock Ticket Request System - Register an Account</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js" type="text/javascript"></script>
<?php if($script){
   echo "<script type=\"text/javascript\">" . $script . "</script>";
}
if($recaptcha){
   echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
}
?>
<style>
   #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
   #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
   #sortable li span { position: absolute; margin-left: -1.3em; }
   </style>
   <script>
   function serialize (mixed_value) {
       // http://kevin.vanzonneveld.net
       // +   original by: Arpad Ray (mailto:arpad@php.net)
       // +   improved by: Dino
       // +   bugfixed by: Andrej Pavlovic
       // +   bugfixed by: Garagoth
       // +      input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
       // +   bugfixed by: Russell Walker (http://www.nbill.co.uk/)
       // +   bugfixed by: Jamie Beck (http://www.terabit.ca/)
       // +      input by: Martin (http://www.erlenwiese.de/)
       // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // -    depends on: utf8_encode
       // %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
       // %          note: Aiming for PHP-compatibility, we have to translate objects to arrays
       // *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
       // *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
       // *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
       // *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'

       var _getType = function (inp) {
           var type = typeof inp, match;
           var key;
           if (type == 'object' && !inp) {
               return 'null';
           }
           if (type == "object") {
               if (!inp.constructor) {
                   return 'object';
               }
               var cons = inp.constructor.toString();
               match = cons.match(/(\w+)\(/);
               if (match) {
                   cons = match[1].toLowerCase();
               }
               var types = ["boolean", "number", "string", "array"];
               for (key in types) {
                   if (cons == types[key]) {
                       type = types[key];
                       break;
                   }
               }
           }
           return type;
       };
       var type = _getType(mixed_value);
       var val, ktype = '';

       switch (type) {
           case "function":
               val = "";
               break;
           case "boolean":
               val = "b:" + (mixed_value ? "1" : "0");
               break;
           case "number":
               val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
               break;
           case "string":
               //mixed_value = this.utf8_encode(mixed_value);
               val = "s:" + encodeURIComponent(mixed_value).replace(/%../g, 'x').length + ":\"" + mixed_value + "\"";
               break;
           case "array":
           case "object":
               val = "a";
               /*
               if (type == "object") {
                   var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
                   if (objname == undefined) {
                       return;
                   }
                   objname[1] = this.serialize(objname[1]);
                   val = "O" + objname[1].substring(1, objname[1].length - 1);
               }
               */
               var count = 0;
               var vals = "";
               var okey;
               var key;
               for (key in mixed_value) {
                   ktype = _getType(mixed_value[key]);
                   if (ktype == "function") {
                       continue;
                   }

                   okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                   vals += this.serialize(okey) +
                           this.serialize(mixed_value[key]);
                   count++;
               }
               val += ":" + count + ":{" + vals + "}";
               break;
           case "undefined": // Fall-through
           default: // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
               val = "N";
               break;
       }
       if (type != "object" && type != "array") {
           val += ";";
       }
       return val;
   }

   $(function() {

     var hookArray;

      $( "#Zone1" ).sortable({ connectWith: '#Zone2, #Zone3, #trashbin',
        start: function (event, ui) {
            $("#Zone1").css('border','1px #000000 solid');
            $("#Zone2").css('border','1px #000000 solid');
            $("#Zone3").css('border','1px #000000 solid');
            $("#trashbin").css('border','1px #000000 solid');
        },
        update:  function (event, ui) {
            if (ui.item.parent().attr('id') == 'Zone1')
               buildArray();
        }
      });
      $( "#Zone1" ).disableSelection();
      $( "#Zone2" ).sortable({ connectWith: '#Zone1, #Zone3, #trashbin',
        start: function (event, ui) {
            $("#Zone1").css('border','1px #000000 solid');
            $("#Zone2").css('border','1px #000000 solid');
            $("#Zone3").css('border','1px #000000 solid');
            $("#trashbin").css('border','1px #000000 solid');
        },
        update:  function (event, ui) {
            if (ui.item.parent().attr('id') == 'Zone2')
                buildArray();
        }
      });
      $( "#Zone2" ).disableSelection();
      $( "#Zone3" ).sortable({ connectWith: '#Zone2, #Zone1, #trashbin',
          start: function (event, ui) {
              $("#Zone1").css('border','1px #000000 solid');
              $("#Zone2").css('border','1px #000000 solid');
              $("#Zone3").css('border','1px #000000 solid');
              $("#trashbin").css('border','1px #000000 solid');
          },
          update:  function (event, ui) {
              if (ui.item.parent().attr('id') == 'Zone3')
                  buildArray();
          }
        });

        $( "#Zone3" ).disableSelection();
        $( "#bottomZone" ).sortable({ connectWith: '#Zone1, #Zone2, #Zone1',
            start: function (event, ui) {
                $("#Zone1").css('border','1px #000000 solid');
                $("#Zone2").css('border','1px #000000 solid');
                $("#Zone3").css('border','1px #000000 solid');
            },
            update:  function (event, ui) {
                if (ui.item.parent().attr('id') == 'bottomZone')
                    buildArray(true);
            }
          });

          $( "#bottomZone" ).disableSelection();
          $( "#trashbin" ).sortable({
              update:  function (event, ui) {
                  if (ui.item.parent().attr('id') == 'trashbin')
                      buildArray(true);
              }
              });
          $( "#trashbin" ).disableSelection();

      function buildArray(reload) {

        hookArray = null;
        hookArray = new Array(3);

          $("#hookContainer").children().each( function(index) {
            if (index < 3) {
                   hookArray[index] = new Array($("#Zone" + (index + 1)).length);
               $("#Zone" + (index + 1)).children().each( function(zoneindex) {
                     hookArray[(index)][zoneindex] = this.id;
               })
            }
          })

          sendToServer(reload);
      }

      function sendToServer(reload) {
               $("#Zone1").sortable({disabled: true});
               $("#Zone2").sortable({disabled: true});
               $("#Zone3").sortable({disabled: true});
               //alert(serialize(hookArray));
            $.post("updateHome.php", { data: serialize(hookArray) }, function(data, reload) {
              if (reload) {
                  location.reload(true)
              }
                  //alert("Data Loaded: " + data);
                  $("#Zone1").sortable({disabled: false});
                  $("#Zone2").sortable({disabled: false});
                  $("#Zone3").sortable({disabled: false});
                  $("#Zone1").css('border','0px #000000 solid');
                  $("#Zone2").css('border','0px #000000 solid');
                  $("#Zone3").css('border','0px #000000 solid');
            }
         )
      }
   }
)

</script>

</head>
<body>
<div id="header"><a <?php if($loggedIn) { ?>href="home.php"<?php }else{ ?>href="index.php"<?php } ?> >
English Wikipedia<br />
Unblock Ticket Request System <?php if(strpos(__FILE__, "/beta/") !== false){ echo "BETA"; } ?>
</a></div>
<?php if($sitenoticeText){?>
<div id="sitenotice">
   <ul>
      <?php echo $sitenoticeText; ?>
   </ul>
</div>
<?php }?>
<div id="subheader">
<ul id="navigation_menu">
<?php if ($loggedIn) { ?>
   <li id="home">
      <a href="<?php echo getRootURL() . 'home.php'; ?>">Home</a>
   </li>
   <li id="stats">
      <a href="<?php echo getRootURL() . 'statistics.php'; ?>">Statistics</a>
   </li>
   <li id="mgmtTemp">
      <a href="<?php echo getRootURL() . 'tempMgmt.php'; ?>">Manage/View Templates</a>
   </li>
   <?php if(verifyAccess($GLOBALS['ADMIN'])) { ?>
   <li id="mgmtUser">
      <a href="<?php echo getRootURL() . 'userMgmt.php'; ?>">Tool Administration</a>
   </li>
   <?php } ?>
   <li id="search">
      <a href="<?php echo getRootURL() . 'search.php'; ?>">Search</a>
   </li>
   <li id="preferences">
      <a href="<?php echo getRootURL() . 'prefs.php'; ?>">Preferences</a>
   </li>
   <li id="privacyPolicy">
      <a href="<?php echo getRootURL() . 'privacy.php'; ?>">Privacy Policy</a>
   </li>
   <li id="logout">
      <a href="<?php echo getRootURL() . 'logout.php'; ?>">Logout</a>
   </li>
<?php } ELSE { ?>
   <li id="appealForm">
      <a href="<?php echo getRootURL() . 'index.php'; ?>">Appeal a Block</a>
   </li>
   <li id="GAB">
      <a href="http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks">Guide to Appealing Blocks</a>
   </li>
   <li id="loginLink">
      <a href="<?php echo getRootURL() . 'loginsplash.php'; ?>">Admins: Log in to review requests</a>
   </li>
   <li id="privacyPolicy">
      <a href="<?php echo getRootURL() . 'privacy.php'; ?>">Privacy Policy</a>
   </li>
<?php } ?>
</ul>
<div style="clear: both"></div>
</div>
<div style="clear: both"></div>
<?php
   //this is for the navigation for the tool admin pages
   if ($adminNav == true) {
      adminNav();
   }
?>
<div id="main">
<?php
}

function skinFooter() {
?>
<br>
<div style="clear:both;"></div>
<div id="footer">
<?php if (loggedIn()) {?>
<p style="text-align:center; font-size:small;">Users active in the last five minutes: <?php echo getLoggedInUsers(); ?></p>
<?php }?>
<p>The Unblock Ticket Request System is a project hosted on the Wikimedia Labs intended to assist
users with the <a href="http://en.wikipedia.org/wiki/Wikipedia:Appealing_a_block" target="_NEW">unblock process</a> on the English Wikipedia. <br />
This project is licensed under the
<a id="GPL" href="http://www.gnu.org/copyleft/gpl.html" target="_NEW">GNU General Public License Version 3 or Later</a>.<br />
For questions or assistance with the Unblock Ticket Request System, please email our administration team at
<a href="mailto:utrs-admins@googlegroups.com">utrs-admins&#64;googlegroups.com</a>.<br />
Version <?php echo getVersion() ?>.</p>
</div>
</body>
</html>
<?php
}

function adminNav() {
?>
<div>
<ul id="adminNav">
   <li id="adminNavHeader">
      <p>Administration</p>
   </li>
   <li id="mgmtTemp">
      <a href="<?php echo getRootURL() . 'tempMgmt.php'; ?>">Manage/View Templates</a>
   </li>
   <li id="mgmtUser">
      <a href="<?php echo getRootURL() . 'userMgmt.php'; ?>">User Management</a>
   </li>
   <li id="banMgmt">
      <a href="<?php echo getRootURL() . 'banMgmt.php'; ?>">Ban Management</a>
   </li>
   <li id="hookMgmt">
      <a href="<?php echo getRootURL() . 'hookMgmt.php'; ?>">Hook Management</a>
   </li>
   <li id="sitenoticeMgmt">
      <a href="<?php echo getRootURL() . 'sitenotice.php'; ?>">Sitenotice Management</a>
   </li>
   <?php if(verifyAccess($GLOBALS['DEVELOPER'])) { ?>
   <li id="massEmail">
      <a href="<?php echo getRootURL() . 'massEmail.php'; ?>">Send Mass Email</a>
   </li>
   <?php } ?>
</ul>
</div>
<?php
}
?>
