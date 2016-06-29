<?php if ( 'on' == et_get_option( 'divi_back_to_top', 'false' ) ) : ?>

	<span class="et_pb_scroll_top et-pb-icon"></span>

<?php endif;

if ( ! is_page_template( 'page-template-blank.php' ) ) : ?>

			<footer id="main-footer">
				<?php get_sidebar( 'footer' ); ?>


		<?php
			if ( has_nav_menu( 'footer-menu' ) ) : ?>

				<div id="et-footer-nav">
					<div class="container">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer-menu',
								'depth'          => '1',
								'menu_class'     => 'bottom-nav',
								'container'      => '',
								'fallback_cb'    => '',
							) );
						?>
					</div>
				</div> <!-- #et-footer-nav -->

			<?php endif; ?>

				<div id="footer-bottom">
                                    <div class="container clearfix">
                                        <div class="et_pb_row footer-block-container">
                                            
                                            <div class="et_pb_column et_pb_column_1_3 footer-logo mobile-element">
                                                <a href="<?php echo get_home_url(); ?>">
                                                    <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" title="Grouks logo." alt="Grouks logo" data-actual-width="211" data-actual-height="67" />
                                                </a>
                                            </div>
                                            
                                            <div class="et_pb_column et_pb_column_1_3 footer-text">
                                                <p id="footer-info">
                                                    Chaussée de saint job 247, <br><br>1180 Bruxelles<br>+32 (0) 479 72 01 26<br><br>contact@grouks.be
                                                </p>
                                            </div>
                                            
                                            <div class="et_pb_column et_pb_column_1_3 footer-logo desktop-element">
                                                <a href="<?php echo get_home_url(); ?>">
                                                    <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" title="Grouks logo." alt="Grouks logo" data-actual-width="211" data-actual-height="67" />
                                                </a>
                                            </div>
                                            
                                            <div class="et_pb_column et_pb_column_1_3 footer-social">
                                                <?php
                                                        if ( false !== et_get_option( 'show_footer_social_icons', true ) ) {
                                                                get_template_part( 'includes/social_icons', 'footer' );
                                                        }
                                                ?>
                                            </div>
                                        </div>
                                    </div>	<!-- .container -->
				</div>
			</footer> <!-- #main-footer -->
		</div> <!-- #et-main-area -->

<?php endif; // ! is_page_template( 'page-template-blank.php' ) ?>

	</div> <!-- #page-container -->

	<?php wp_footer(); ?>
</body>
</html>