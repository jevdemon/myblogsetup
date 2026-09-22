<style>
@media (min-width: 992px) {
    .container.page-body {
        max-width: 1600px !important;
    }
}
.blogroll-sidebar {
    position: fixed;
    top: 120px;
    width: 210px;
    font-size: 1.15em;
    line-height: 1.5;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 1em;
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}
.about-sidebar {
    position: fixed;
    top: 120px;
    width: 210px;
    font-size: 1.15em;
    line-height: 1.5;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 1em;
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}
@media (min-width: 992px) {
    .blogroll-sidebar {
        right: max(20px, calc((100vw - 1600px) / 2 + 20px));
    }
    .about-sidebar {
        left: max(20px, calc((100vw - 1600px) / 2 + 20px));
    }
}
@media (max-width: 991px) {
    .blogroll-sidebar,
    .about-sidebar {
        position: static;
        width: auto;
        max-width: 700px;
        margin: 3em auto;
        padding: 1em;
        font-size: 1em;
        max-height: none;
        overflow-y: visible;
    }
}
.blogroll-sidebar h3, .about-sidebar h3 { font-size: 1.3em; margin-top: 0; }
.blogroll-sidebar h4 { font-size: 1.1em; margin-bottom: 0.3em; }
.blogroll-sidebar ul { padding-left: 1.2em; margin-bottom: 1.2em; }
</style>

<div class="about-sidebar">
<?php include dirname(__FILE__) . '/about-content.tpl.php'; ?>
</div>

<div class="blogroll-sidebar">
<h3>Linkblog</h3>
<?php include dirname(__FILE__) . '/linkblog-content.tpl.php'; ?>
</div>
