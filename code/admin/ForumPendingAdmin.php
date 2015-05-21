<?php
class ForumPendingAdmin extends ModelAdmin {

	private static $menu_icon = 'irxnews/images/icons/news_icon.png';

	private static $title       = 'Pending Forum';
	private static $menu_title  = 'Pending Forum';
	private static $url_segment = 'forum';

	private static $managed_models  = array('Post', 'Member');
	private static $model_importers = array();

}