<?php
require_once __DIR__ . '/../config/config.php';
$page = $page ?? 'home';
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create stunning AI-generated presentations from any topic in seconds.">
    <title><?php echo htmlspecialchars($pageTitle); ?> — <?php echo APP_NAME; ?></title>

    <!-- Prevent theme flash -->
    <script>
      (function() {
        try {
          var t = localStorage.getItem('appTheme');
          if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
          document.documentElement.setAttribute('data-theme', t);
        } catch(e) {}
      })();
    </script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    <?php if ($page === 'generator'): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/generator.css">
    <?php endif; ?>
    <?php if ($page === 'editor'): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/editor.css">
    <?php endif; ?>
    <?php if ($page === 'slideshow'): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/editor.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/slideshow.css">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <script>window.APP_BASE_URL = "<?php echo BASE_URL; ?>";</script>
</head>
<body class="page-<?php echo htmlspecialchars($page); ?>">

<header class="app-navbar" id="appNavbar">
    <div class="nav-inner">
        <a href="<?php echo BASE_URL; ?>/index.php" class="nav-brand">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16v12H4z"></path>
                    <path d="M2 20h20"></path>
                    <path d="M9 10l2 2 4-4"></path>
                </svg>
            </span>
            <span class="brand-text">AI PPT<span class="brand-accent">Generator</span></span>
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="<?php echo BASE_URL; ?>/index.php" class="<?php echo $page==='home'?'active':''; ?>">Home</a>
            <a href="<?php echo BASE_URL; ?>/generator.php" class="<?php echo $page==='generator'?'active':''; ?>">Create</a>
            <a href="<?php echo BASE_URL; ?>/saved-presentations.php" class="<?php echo $page==='saved'?'active':''; ?>">My Decks</a>
            <a href="<?php echo BASE_URL; ?>/index.php#features">Features</a>
            <a href="<?php echo BASE_URL; ?>/index.php#faq">FAQ</a>
        </nav>

        <div class="nav-actions">
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle theme">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4.5"></circle>
                    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>
                </svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path>
                </svg>
            </button>
            <a href="<?php echo BASE_URL; ?>/generator.php" class="btn btn-primary btn-sm nav-cta">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                New
            </a>
            <button id="mobileMenu" class="mobile-menu-btn" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<main class="app-main">
