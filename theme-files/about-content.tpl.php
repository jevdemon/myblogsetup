<h3>What Is This?</h3>
<p>A space for stuff I find interesting.</p>
<p>I'm a former CTO, Software Developer, Consultant, and Technical and Industry Standards expert.</p>
<p><a href="https://r2group.ca">Contact or work with me here.</a></p>
<p>Memento Mori<br>Amor Fati</p>
<?php
$photoDir = $_SERVER['DOCUMENT_ROOT'] . '/blog/gfx/bio-photos/';
$photoUrlBase = 'https://evdemon.org/blog/gfx/bio-photos/';
$photos = glob($photoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
sort($photos);
if (count($photos) > 0) {
    $index = date('z') % count($photos);
    $chosenFile = $photos[$index];
    $photoUrl = $photoUrlBase . basename($chosenFile);
} else {
    $chosenFile = $_SERVER['DOCUMENT_ROOT'] . '/blog/gfx/future.jpg';
    $photoUrl = 'https://evdemon.org/blog/gfx/future.jpg';
}
// get the size of the photo so the pop up window can be sized appropriately
$dimensions = @getimagesize($chosenFile);
if ($dimensions) {
    $popupWidth = $dimensions[0] + 20;
    $popupHeight = $dimensions[1] + 20;
} else {
    $popupWidth = 400;
    $popupHeight = 400;
}

echo '<a href="' . $photoUrl . '" target="_blank" onclick="window.open(this.href, \'bioPhoto\', \'width=' . $popupWidth . ',height=' . $popupHeight . ',resizable=yes,scrollbars=yes\'); return false;"><img style="width:200px;" src="' . $photoUrl . '"></a>';
?>
