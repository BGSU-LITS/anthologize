<!doctype html>
<html>
	<head>
		<title><?php echo $project->title(); ?></title>
		<style type="text/css">
			body{ font-size:<?php echo $font_size; ?>px; }
			h1{ font-size:2em; }
			h2{ font-size:1.8em; }
			h3{ font-size:1.6em; }
		</style>
	</head>

	<body>
		<h1><?php echo $project->title(); ?></h1>

		<h2>Dedication</h2>
		<p><?php echo $project->meta('dedication', ""); ?></p>

		<h2>Acknowledgements</h2>
		<p><?php echo $project->meta('acknowledgements', ""); ?></p>


<?php foreach ($project->parts() as $part): ?>
	<?php if (count($part->posts() > 0)): ?>
		<h2><?php echo $part->title(); ?></h2>

		<?php foreach ($part->posts() as $post): ?>
		<h3><?php echo $post->title(); ?></h3>

		<?php if (count($post->tags())): ?>
		<p>Tags</p>
		<ul>
		<?php foreach ($post->tags() as $tag): ?>
			<li><a href=""></a></li>
		<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<?php if (count($post->categories())): ?>
		<p>Categories</p>
		<ul>
		<?php foreach ($post->categories() as $category): ?>
			<li><a href="<?php echo get_site_url()."?cat={$category->term_id}"; ?>"><?php echo $category->name; ?></a></li>
		<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<div class="item-meta">
			<img class="gravatar" src="<?php echo $post->gravatar_url(); ?>" />
			<p class="item-author">By <?php echo $post->author(); ?></p>
			<p class="item-anthologizer">Anthologized by: <?php $project->anthologizer(); ?></p>
			<p class="item-asserted-author">Attributed to: <?php $project->asserted_author(); ?></p>
		</div>
		<div class="item-content">
			<?php echo $post->content($do_shortcodes); ?>
		</div>

		<!-- TODO: Add Comments -->
		<?php endforeach; ?>
	<?php endif; ?>
<?php endforeach; ?>
	</body>

</html>
