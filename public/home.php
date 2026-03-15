<?php
// Page d'accueil incluse via page.php?p=home
// Peut aussi être utilisée comme démonstration de LFI
?>
<html><body>
<h2>Page d'accueil</h2>
<p>Cette page est incluse via <code>page.php?p=home</code>.</p>
<p>Essaie <code>page.php?p=../../../../etc/passwd</code> pour exploiter la faille LFI.</p>
</body></html>
