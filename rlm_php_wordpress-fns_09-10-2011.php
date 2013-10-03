<?php

/* ----- Add Post Thumbnails ------------------------------------------------------------ */
if(function_exists('add_theme_support')){
	add_theme_support('post-thumbnails');
	add_theme_support('menus');
}

/* ----- Configure Sidebars ------------------------------------------------------------ */
$sidebar_html = array(
	'before_widget' => '<nav id="%1$s" class="mod-list %2$s">',
	'after_widget' => '</nav>',
	'before_title' => '<header class="list-head">',
	'after_title' => '</header>',
	'before_body' => '<div class="wrap list-body">',
	'after_body' => '</div>'
);
add_action('init', 'theme_sidebars');
function theme_sidebars(){
	if(function_exists('register_sidebar')){
		register_sidebar(array(
			'name' => 'Site Tagline',
			'id' => 'site-tagline',
			'description' => 'Site Tagline is displayed next to the Site Title and under the Site Menu.',
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => ''
		));
		register_sidebar(array(
			'name' => 'Home Aside',
			'id' => 'home-aside',
			'description' => 'Home Aside is displayed as a sidebar (below Page Footer) on the post home page only.',
			'before_widget' => '<section id="%1$s" class="%2$s">',
			'after_widget' => '</section>',
			'before_title' => '<header>',
			'after_title' => '</header>'
		));
		register_sidebar(array(
			'name' => 'Page Aside',
			'id' => 'page-aside',
			'description' => 'Page Aside is displayed as a sidebar (below Page Footer).',
			'before_widget' => '<nav id="%1$s" class="mod-list %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="list-head">',
			'after_title' => '</header><div class="wrap list-body">'
		));
		register_sidebar(array(
			'name' => 'Page Footer',
			'id' => 'page-foot',
			'description' => 'Page Footer is displayed as a sidebar (above Page Aside).',
			'before_widget' => '<nav id="%1$s" class="mod-list %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="list-head">',
			'after_title' => '</header><div class="wrap list-body">'
		));
		register_sidebar(array(
			'name' => 'Post Aside',
			'id' => 'post-aside',
			'description' => 'Post Aside is displayed as a sidebar (below Post Footer).',
			'before_widget' => '<nav id="%1$s" class="mod-list %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="list-head">',
			'after_title' => '</header><div class="wrap list-body">'
		));
		register_sidebar(array(
			'name' => 'Post Footer',
			'id' => 'post-foot',
			'description' => 'Post Footer is displayed as a sidebar (above Post Aside).',
			'before_widget' => '<nav id="%1$s" class="mod-list %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="list-head">',
			'after_title' => '</header><div class="wrap list-body">'
		));
		register_sidebar(array(
			'name' => 'Archive Aside',
			'id' => 'archive-aside',
			'description' => 'Archive Aside is displayed as a sidebar.',
			'before_widget' => '<nav id="%1$s" class="mod-cloud %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="cloud-head">',
			'after_title' => '</header><div class="wrap cloud-body">'
		));
		register_sidebar(array(
			'name' => 'Tag Aside',
			'id' => 'tag-aside',
			'description' => 'Tag Aside is displayed as a sidebar.',
			'before_widget' => '<nav id="%1$s" class="mod-cloud %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="cloud-head">',
			'after_title' => '</header><div class="wrap cloud-body">'
		));
		register_sidebar(array(
			'name' => 'Category Aside',
			'id' => 'category-aside',
			'description' => 'Category Aside is displayed as a sidebar.',
			'before_widget' => '<nav id="%1$s" class="mod-cloud %2$s">',
			'after_widget' => '</div></nav>',
			'before_title' => '<header class="cloud-head">',
			'after_title' => '</header><div class="wrap cloud-body">'
		));
	}
	return;
} // function theme_sidebars

function is_sidebar_active($index){
	global $wp_registered_sidebars;
	$widgetcolumns = wp_get_sidebars_widgets();
	if(array_key_exists($index, $widgetcolumns) && $widgetcolumns[$index]) return true;
	return false;
} // function is_sidebar_active

/* ----- Configure Menus ------------------------------------------------------------ */
add_action('init', 'theme_menus');
function theme_menus(){
	if(function_exists('register_nav_menu')){
		register_nav_menu('site-menu', __('Site Menu'));
	}
} // function theme_menus(

function is_menu_active($index){
	if(has_nav_menu($index)) return true;
	return false;
} // function is_menu_active

?>
