# Idno Blog Customizations

Backup of custom files for my Idno (formerly Known) install at evdemon.org/blog.

## Install context

- Idno version: 1.6.4
- Theme: Black (displays as "Tabula Rasa")
- PHP: 8.3+
- Hosting: shared cPanel (Reclaim Hosting), subdirectory install

## Install steps (from scratch)

1. Run: composer create-project idno/known . -s dev (falls back to stable 1.6.4)
2. Set PHP 8.3+ for the directory via .htaccess, adding this block:

       <IfModule mime_module>
           AddHandler application/x-httpd-ea-php83 .php
       </IfModule>

3. Create a MySQL database and run the web installer.

## What's in this repo

- theme-files/ — goes in Themes/Black/templates/default/shell/ in the Idno install
  - footerjavascript.tpl.php — custom rich text editor (contenteditable-based, replaces Idno's broken TinyMCE integration), paragraph/heading permalinks, broken-preview auto-hider
  - aftercontainer.tpl.php — About and Linkblog sidebar layout/CSS
  - about-content.tpl.php — bio text + daily-rotating photo (PHP picks a file from gfx/bio-photos/ based on day of year)
  - blogroll-content.tpl.php — static blogroll links
  - linkblog-content.tpl.php — fetches and caches a FeedLand RSS river
- endpoints/ — goes in the Idno install's web root (same level as index.php)
  - simple-upload.php — authenticated photo upload endpoint (checks real Idno login via Idno::site()->session()->isLoggedIn())
  - rotate-photo.php — server-side photo rotation via GD
  - append-to-post.php — appends new content to an existing post using Idno's own Entry model and publish() method (requires registering a stub Page object first — see comments in file — to work around a bug in Idno's syndication step when publish() is called outside a normal request)
  - list-recent-posts.php — lists the 3 most recent posts, used by the append flow
- custom.css — goes in Known's admin → Custom CSS panel

## Known upstream bugs these files work around

- TinyMCE editor fails to init ($(...).tinymce is not a function) — hence the custom editor
- Entity::publish() crashes with "Call to a member function getInput() on false" when called outside a normal page request (no currentPage() set) — hence the stub Page workaround in append-to-post.php
