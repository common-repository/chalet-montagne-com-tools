<div id="locationGallery" class="cmGallery">
<?php

	foreach($images as $imageData){
		echo '<a data-fancybox="gallery" data-caption="'.$imageData['caption'].'" href="'.$imageData['img'].'"><img class="imgFancyGallery" src="'.$imageData['thumb'].'"></a>';
	}

?>
</div>
