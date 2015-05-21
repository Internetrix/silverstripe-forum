<?php
class ForumPendingAdmin extends ModelAdmin {

	private static $menu_icon = 'irxnews/images/icons/news_icon.png';

	private static $title       = 'Pending Forum';
	private static $menu_title  = 'Pending Forum';
	private static $url_segment = 'forum';

	private static $managed_models  = array('Post', 'Group');
	private static $model_importers = array();
	
	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
	
		$gridFieldName = $this->sanitiseClassName($this->modelClass);
		$gridField = $form->Fields()->fieldByName($gridFieldName);
	
		// modify the list view.
		$gridFieldConfig = $gridField->getConfig();
	
		$gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
		$gridFieldConfig->removeComponentsByType('GridFieldPrintButton');
		$gridFieldConfig->removeComponentsByType('GridFieldExportButton');
		
		if($this->modelClass == 'Post') {
			$gridFieldConfig->addComponent(new GridField_ApprovePostAction());
		}
		
		if($this->modelClass == 'Group') {
			//Remove the delete button
			$gridFieldConfig->removeComponentsByType('GridFieldDeleteAction');
			
			// Modify the detail form
			$detailForm = $gridFieldConfig->getComponentByType('GridFieldDetailForm');
			$detailForm->setItemEditFormCallback(function($form, $component) {
				$fields = $form->Fields();
				$groupId = $fields->dataFieldByName('ID')->Value();
				
				$group = Group::get()->byID($groupId);
				$members = $group->Members()->filter("Approved", false);

				// Remove tabs
				$fields->removeByName("Members");
				$fields->removeByName("Permissions");
				$fields->removeByName("Roles");
				$fields->removeByName("Subsites");
				$fields->removeByName("RestrictedArea");
				$fields->removeByName("Forum");
				
				// GridFieldConfig
				$gridFieldConfig = GridFieldConfig_RecordEditor::create();
				$gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
				$gridFieldConfig->getComponentByType('GridFieldDataColumns')->setDisplayFields(array ('FirstName'=>'FirstName','Surname'=>'Surname','Email'=>'Email', 'Approved' => 'Approved'));
				$gridFieldConfig->addComponent(new GridField_ApproveUserAction());
				
				//$fields->addFieldToTab("Root.Members", HeaderField::create('Test'));
				$fields->addFieldToTab("Root.Members", $gridField = new GridField('Members', 'Members', $members, $gridFieldConfig));
				$gridField->setForm($form);
				
			});
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
		
		if($this->modelClass == 'Group') {
			$list = $list->filter('IsForumGroup', true);
		}
	
		return $list;
	}

}