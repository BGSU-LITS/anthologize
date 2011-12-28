<!doctype html>
<html>
	<head>
		<title><?php echo $project->title(); ?></title>
		<style type="text/css">
			body{ font-size:<?php echo $font_size; ?>px; }
			h1{ font-size:2em; }
			h2{ font-size:1.8em; }
			h3{ font-size:1.6em; }
			h4{ font-size:1.4em; }
		</style>
	</head>

	<body>
		<h1><?php echo $project->title(); ?></h1>

		<?php if($dedication): ?>
		<div id="dedication">
			<h2>Dedication</h2>
			<p><?php echo apply_filters('the_content', $dedication); ?></p>
		</div>
		<?php endif; ?>

		<?php if($acknowledgements): ?>
		<div id="acknowledgements">
			<h2>Acknowledgements</h2>
			<p><?php echo apply_filters('the_content', $acknowledgements); ?></p>
		</div>
		<?php endif; ?>

<?php foreach ($project->parts() as $part): ?>
		<div id="parts">
	<?php if (count($part->posts() > 0)): ?>
		<h2><?php echo $part->title(); ?></h2>

		<?php foreach ($part->posts() as $post): ?>
		<h3><?php echo $post->title(); ?></h3>

		<?php if (count($post->tags())): ?>
		<p>Tags</p>
		<ul>
		<?php foreach ($post->tags() as $tag): ?>
			<li><a href="<?php echo get_site_url()."?tag={$tag->slug}"; ?>"><?php echo $tag->name; ?></a></li>
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

		<div class="post-meta">
			<img class="gravatar" src="<?php echo $post->gravatar_url(); ?>" />
			<p class="post-author">By <?php echo $post->author(); ?></p>
			<p class="post-anthologizer">Anthologized by: <?php $project->anthologizer(); ?></p>
			<p class="post-asserted-author">Attributed to: <?php $project->asserted_author(); ?></p>
		</div>
		<div class="post-content">
			<?php echo $post->content($do_shortcodes); ?>
		</div>

		<?php if (count($post->comments())): ?>
		<div class="post-comments">
			<h4>Comments</h4>
		<?php foreach ($post->comments() as $comment): ?>
			<div class="post-comment">
				<p>
					Posted by: <span class="comment-name"><?php echo $comment->author(); ?></span><br />
					Posted on: <span class="comment-date"><?php echo $comment->posted_date(); ?></span>
				</p>

				<div class="comment-content"><?php echo $comment->content(); ?></div>
			</div>
		<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
		</div>
<?php endforeach; ?>
	</body>

</html>
