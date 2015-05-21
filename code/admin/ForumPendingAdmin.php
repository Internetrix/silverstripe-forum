<?php
class ForumPendingAdmin extends ModelAdmin {

	private static $menu_icon = 'irxnews/images/icons/news_icon.png';

	private static $title       = 'Pending Forum';
	private static $menu_title  = 'Pending Forum';
	private static $url_segment = 'forum';

	private static $managed_models  = array('Post', 'Member');
	private static $model_importers = array();
	
	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		
		if($this->modelClass == 'Post') {
			$gridField = $form->Fields()->fieldByName("Post");
		
			// modify the list view.
			$gridFieldConfig = $gridField->getConfig();
		
			$gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
			$gridFieldConfig->removeComponentsByType('GridFieldPrintButton');
			$gridFieldConfig->removeComponentsByType('GridFieldExportButton');
			$gridFieldConfig->addComponent(new GridField_ApprovePostAction());
		}
		
		return $form;
	}
	
	public function getList() {
		$list = parent::getList();
		$params = $this->request->requestVar('q'); // get the search params
		
		// For the posts model, filter the results so only the Awaiting posts are shown
		// But if a search paramater is used, show according to that
		if($this->modelClass == 'Post' && !(isset($params['Status']) && $params['Status'])) {
			$list = $list->filter('Status', 'Awaiting');
		}
	
		return $list;
	}

}