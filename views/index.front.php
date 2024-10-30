<?php foreach($content as $key => $loc): ?>
	<article class="cmci_loc_block">
		<header>
			<h1><a href="#" title="<?php echo $loc["content"]->nom; ?>"><?php echo $loc["content"]->nom; ?></a></h1>
		</header>
		<section class="cmci_content">
			<div class="cmci_img_container">
				<?php if (sizeof($loc["content"]->images) > 1): ?>
					<ul id="cmci_<?php echo $loc["content"]->id; ?>" class="cmci_slider">
					<?php foreach ($loc["content"]->images as $k => $v): ?>
						<li><img src="<?php echo plugins_url('chalet-montagne/images_saved/' . $loc["content"]->id .'/'.$k.'.jpg'); ?>"></li>
					<?php endforeach ?>
					</ul>
				<?php else: ?>
					<?php foreach ($loc["content"]->images as $k => $v): ?>
						<img src="<?php echo plugins_url('chalet-montagne/images_saved/' . $loc["content"]->id .'/'.$k.'.jpg'); ?>">
					<?php endforeach ?>
				<?php endif; ?>
			</div>
			<div class="cmci_fixclear"></div>
			<div class="cmci_description_container">
				<?php if(get_option('WPLANG') === 'fr_FR'): ?>
					<p><?php echo $loc["content"]->descriptif_fr; ?></p>
					
				<?php else: ?>
					<p><?php echo $loc["content"]->descriptif_en; ?></p>
				<?php endif; ?>
			</div>
		</section>

		<footer>
			<p><?php esc_html_e('Rendez-vous sur :', 'chalet-montagne'); ?> <a href="http://chalet-montagne.com">www.chalet-montagne</a></p>
		</footer>
	</article>
<?php endforeach; ?>