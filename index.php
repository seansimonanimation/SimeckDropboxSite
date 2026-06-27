<?php
ob_start();
   //Necessary to begin the session.

   include_once __DIR__ . '/libraries/session.php'; //One of only 2 __DIR__s in the entire codebase, since session.php needs to be included before we can use __ROOT__.
   include_once __ROOT__ . '/libraries/auth.php';
   include_once __ROOT__ . '/libraries/topBarPhrases.php';
   include_once __ROOT__ . '/libraries/moduleLoader.php';
   include_once __ROOT__ . '/libraries/setHandlers.php';
   require_once __ROOT__ . '/vendor/autoload.php';
   require_once __ROOT__ . '/libraries/logging.php';

function adminViewToggle(){
    if(GetRole() == 'admin' && GetTempRole() == 'admin'){
        $_SESSION['tempRole'] = 'artist';
    } else if(GetRole() == 'admin' && GetTempRole() == 'artist'){
        $_SESSION['tempRole'] = 'admin';
    }
   $moduleName = $_SESSION['CurrentModuleName'] ?? 'Dashboard'; // Default to Dashboard if not set
    
    try {
        SetActiveModule($moduleName);
    } catch (Exception $e) {
        // Handle the case where ActiveModule is not set or invalid
        SetActiveModule('Dashboard'); // Default to Dashboard if there's an issue
    }
}
function adminSwitchViewButtonActivation(){
    if(GetRole() == 'admin' && !IsImpersonating()){
        return '<a href="index.php?action=switch_role">Switch Role</a>';
    } else {
        return '';
    }
}

   ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <link rel="stylesheet" href="css/portal.css">
   <link rel="stylesheet" href="css/moduleStyle.css">
   <?php
      $currentTheme = $_SESSION['theme'] ?? 'dark-boo';
      $themeFile = 'css/siteThemes/' . $currentTheme . '.css';
      if(file_exists(__ROOT__ . '/' . $themeFile)){
         echo '<link rel="stylesheet" href="' . $themeFile . '?v=' . filemtime(__ROOT__ . '/' . $themeFile) . '">';
      } else {
         // Fallback to Dark Boo
         echo '<link rel="stylesheet" href="css/siteThemes/dark-boo.css">';
      }
?>
   <title>Simeck Entertainment <?php echo GetTempRole(); ?> Portal<br /></title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

</head>
<body class="portal-layout <?php echo GetThemeClass(); ?> <?php echo htmlspecialchars($_SESSION['ActiveModule'] ?? ''); ?><?php if (!empty($_SESSION['bgvid_visibility'])): ?> bgvid-active<?php endif; ?>">

<!-- Mobile navigation toggle -->
<button id="mobile-nav-toggle" class="mobile-nav-toggle" aria-label="Open navigation menu">☰</button>
<div id="mobile-nav-backdrop" class="mobile-nav-backdrop"></div>

<?php if (!empty($_SESSION['bgvid_visibility'])): ?>
<div class="theme-bg-video" aria-hidden="true">
  <video autoplay muted loop playsinline></video>
</div>
<?php endif; ?>


   <aside id="sidebar" role="navigation">

      <div class="sidebar-header"><?php echo $_SESSION['tempRole']; ?> portal
   <br /> <?php echo adminSwitchViewButtonActivation(); ?></div>
   <nav>
   <!-- Sidebar content goes here -->
    <?php echo LoadNavbarContent(); ?>
</nav>

     <div class="sidebar-footer">
         <?php if(IsImpersonating()): ?>
             <a href="index.php?action=stop_impersonating" class="stop-impersonate-btn">← Stop Impersonating</a>
         <?php elseif(GetRole() === 'admin' && GetTempRole() === 'admin'): ?>
             <form method="GET" action="index.php" class="impersonate-form">
                 <input type="hidden" name="action" value="impersonate_client" />
                 <label class="sidebar-label">Impersonate Client</label>
                 <select name="client" class="impersonate-select" onchange="this.form.submit()">
                     <option value="">— Select —</option>
                     <?php
                         $clients = ListAllActiveClients();
                         foreach($clients as $c){
                             echo '<option value="' . htmlspecialchars($c['username']) . '">'
                                  . htmlspecialchars($c['firstname'] . ' ' . $c['lastname'])
                                  . '</option>';
                         }
                     ?>
                 </select>
             </form>
             <form method="GET" action="index.php" class="impersonate-form">
                 <input type="hidden" name="action" value="impersonate" />
                 <label class="sidebar-label">Impersonate Artist</label>
                 <select name="artist" class="impersonate-select" onchange="this.form.submit()">
                     <option value="">— Select —</option>
                     <?php
                        $artists = ListAllActiveArtists();
                        foreach($artists as $a){
                            echo '<option value="' . htmlspecialchars($a['username']) . '">'
                                 . htmlspecialchars(GetArtistNicknameAndLegalName($a))
                                 . '</option>';
                        }

                     ?>
                 </select>
             </form>
         <?php endif; ?>
         <a href="index.php?action=logout">Logout</a>
     </div>


   </aside>
   <header id="topbar" role="banner">
<div class="topbar-title">
            <?php if(IsImpersonating()){
                echo '<span class="read-only-badge">❄️❄️READ ONLY MODE ENGAGED!!! Impersonating: ' . GetHumanName('greeting') . ' (' . GetTempRole() . ')❄️❄️</span>';
            } else {
                echo 'Hi, ' . GetHumanName('greeting') . '!';

            }
             ?>
             </div>
             <div class="topbar-phrase">
</div>
<div class="topbar-right"><?php echo DisplayRandomTopbarPhrase(); ?></div>
</header>
<main id="content">
   <?php echo DisplayActiveModuleContent(); ?>
</main>

<script>(function() {
  // Window Guard: only initialize once
  if (window.__fi_activeWindowInited) return;
  window.__fi_activeWindowInited = true;

  var baseZ = 9999;
  var maxZ = baseZ;

  document.addEventListener('mousedown', function(e) {
    var island = e.target.closest('.floating-island');
    if (!island) return;
    maxZ++;
    island.style.zIndex = maxZ;
  });
  })();

  /* ── Theme background video ────────────────────────────── */
  (function() {
    var style = getComputedStyle(document.body);
    var src  = style.getPropertyValue('--theme-bg-video-src').trim();
    var opacity = style.getPropertyValue('--theme-bg-video-opacity').trim();
    if (src) {
      src = src.replace(/^['"]|['"]$/g, '');
      if (src) {
        var vid = document.querySelector('.theme-bg-video video');
        if (vid) {
          if (opacity) vid.style.setProperty('opacity', opacity);
          var source = document.createElement('source');
          source.src   = src;
          source.type  = 'video/mp4';
          vid.appendChild(source);
          vid.load();
        }
      }
    }
  })();
</script>

<script>
  /* ── Mobile navigation: slide-from-top sidebar ────────────────────── */
  (function() {
    var toggle = document.getElementById('mobile-nav-toggle');
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('mobile-nav-backdrop');
    if (!toggle || !sidebar || !backdrop) return;

    function openNav() {
      sidebar.classList.add('mobile-nav-open');
      backdrop.classList.add('visible');
      toggle.setAttribute('aria-label', 'Close navigation menu');
      toggle.innerHTML = '✕';
      document.body.style.overflow = 'hidden';
    }
    function closeNav() {
      sidebar.classList.remove('mobile-nav-open');
      backdrop.classList.remove('visible');
      toggle.setAttribute('aria-label', 'Open navigation menu');
      toggle.innerHTML = '☰';
      document.body.style.overflow = '';
    }
    toggle.addEventListener('click', function() {
      if (sidebar.classList.contains('mobile-nav-open')) {
        closeNav();
      } else {
        openNav();
      }
    });
    backdrop.addEventListener('click', closeNav);
    // Auto-close when a navigation link is clicked
    var navLinks = sidebar.querySelectorAll('nav a');
    for (var i = 0; i < navLinks.length; i++) {
      navLinks[i].addEventListener('click', closeNav);
    }
  })();
</script>

   </body>
</html>