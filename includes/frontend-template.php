<?php $form_id = 1;

?>
<html class="no-js" <?php language_attributes();	 ?>>

	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" >

		<link rel="profile" href="https://gmpg.org/xfn/11">
		<?php gravity_form_enqueue_scripts( $form_id, false ); ?>
		<?php wp_head(); ?>

	</head>

	<body <?php body_class(); ?>>

		<?php
		wp_body_open();
		wp_enqueue_style( 'gfcf_template_css' );
		?>
   

   <div class="gfcf-core-main wp-block-wpm-gfcf-core sk-content-body wpm-gfcf-template-default">


		<?php the_content(); ?>



</div>
<?php wp_footer(); ?>

	</body>
</html>