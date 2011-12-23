<!doctype html>
<html>
	<head>
		<title><?php echo $api->project->post_title; ?></title>
	</head>

	<body>
		<h1><?php echo $api->project->post_title; ?></h1>

		<?php foreach($api->parts as $part): ?>

			<h2><?php echo $part->post_title; ?></h2>
			<?php foreach($api->posts($part->ID) as $post): ?>
				<h3><?php echo $post->post_title; ?></h3>

				<?php echo $post->post_content; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>

	<?php

	anth_section('body');
	while ( anth_parts() ) {

		anth_part();

		if ( anth_part_has_items() ) { // Anthologize assumes part_id from context

			?>

			<h2><?php anth_the_title(); ?></h2>
			<?php

			while( anth_part_items() ) {
				anth_item();
				echo "<p>Tags</p><ul>";
				while( anth_tags() ) {
					anth_tag_details();
					echo "<li>";
					echo "<a href='" . anth_get_the_tag_detail('url') . "'>"  . anth_get_the_tag() . "</a>";
					echo "</li>";
				}
				echo "</ul>";

				echo "<p>Categories</p><ul>";
				while( anth_categories() ) {
					anth_category_details();
					echo "<li>";
					echo "<a href='" . anth_get_the_category_detail('url') . "'>"  . anth_get_the_category() . "</a>";
					echo "</li>";
				}
				echo "</ul>";

				anth_person_details();
				anth_person_details('anthologizer');

				?>
				<h3><?php anth_the_title() ?></h3>
				<div class="item-meta" style="border: 1px solid black; margin: 5px; padding: 5px;">

					<img class="gravatar" src="<?php anth_the_person_gravatar_url(); ?>" />
					<p class="item-author">By <?php anth_the_person(); ?></p>
					<p class="item-anthologizer">Anthologized by: <?php anth_the_person('anthologizer'); ?></p>
					<p class="item-asserted-author">Attributed to: <?php anth_the_person('assertedAuthor'); ?></p>
				</div>
				<div class="item-content">
					<?php anth_the_item_content() ?>
				</div>

				<?php
			}
		}
	}
	?>



	</body>

</html>
