<?php
/*
$HEADING: The HTML title
$ECHO: The main content
$ERRMSG: array of error messages
$WARNMSG: array of warnings
$SUCCMSG: array of success messages
*/
if (!defined('INVDB'))
    die('No access');


?><!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InvDB <?=$HEADING?></title>
    <link href="html/bootstrap.min.css" rel="stylesheet">
</head>
<body><div class="container">

    <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
      <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
        <img class="bi me-2" width="40" height="32" src="imgs/athene.svg">
        <span class="fs-4">UniBwM Inventarisierungsdatenbank</span>
      </a>

      <ul class="nav nav-pills">
        <?php
        // print the main menu
        foreach ($actions as $act) {
          if (($act->menus & 1) != 0 && ($act->isActive() || $act->isInactiveVisible())) {
            echo '<li class="nav-item"><a href="'
            . 'index.php?action=' . $act->short
            . ($act->csrf ? '&CSRF=' . $SESS['CSRF'] : '')
            . '" class="nav-link'
            . (!$act->isActive() ? ' disabled' : '')
            . ($ACTION == $act->short ? ' active' : '')
            . '">' . $act->long . '</a></li>';
          }
        }
        ?>
      </ul>
    </header>

    <?php
    if (!empty($ERRMSG)) {
      echo '<div class="alert alert-danger" role="alert">';
      if (count($ERRMSG) > 1) {
        echo '<ul>';
        foreach ($ERRMSG as $msg) {
          echo "<li>$msg</li>";
        }
        echo '</ul>';
      }
      else
        echo $ERRMSG[0];
      echo '</div>';
    }
    if (!empty($WARNMSG)) {
      echo '<div class="alert alert-warning" role="alert">';
      if (count($WARNMSG) > 1) {
        echo '<ul>';
        foreach ($WARNMSG as $msg) {
          echo "<li>$msg</li>";
        }
        echo '</ul>';
      }
      else
        echo $WARNMSG[0];
      echo '</div>';
    }
    if (!empty($SUCCMSG)) {
      echo '<div class="alert alert-success" role="alert">';
      if (count($SUCCMSG) > 1) {
        echo '<ul>';
        foreach ($SUCCMSG as $msg) {
          echo "<li>$msg</li>";
        }
        echo '</ul>';
      }
      else
        echo $SUCCMSG[0];
      echo '</div>';
    }
    ?>
    
    <?=$ECHO?>
    
    <footer class="py-3 my-4">
        <ul class="nav justify-content-center border-bottom pb-3 mb-3">
          <?php
          foreach ($actions as $act) {
          if (($act->menus & 2) != 0 && ($act->isActive() || $act->isInactiveVisible())) {
            echo '<li class="nav-item"><a href="'
            . 'index.php?action=' . $act->short . '" class="nav-link px-2 text-muted'
            . (!$act->isActive() ? ' disabled' : '')
            . '">' . $act->long . '</a></li>';
          }
        }
        ?>
        </ul>
        <p class="text-center text-muted">&copy; <?php echo date('Y'); ?> UniBwM</p>
    </footer>
    
</div>
<script src="html/jquery.min.js"></script>
<script src="html/bootstrap.bundle.min.js"></script>
</body>
</html>