<?php
class ForumGroupExtension extends DataExtension {
	private static $db = array(
		'IsForumGroup'				=> 'Boolean',
		'UserModerationRequired'	=> 'Boolean'
	);
	
	private static $many_many = array(
		'Moderators'				=> 'Member'
	);
	
	private static $many_many_extraFields = array(
		'Members'					=> array('Approved' => 'Boolean')
	);
	
	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab('Root.Members', CheckboxField::create('IsForumGroup'), 'Description');
		
		if($this->owner->ID && $this->owner->IsForumGroup) {
			$membersGridfield = $fields->dataFieldByName("Members")->getConfig();
			$membersGridfield->removeComponentsByType('GridFieldAddNewButton');
			$membersGridfield->addComponent(new GridFieldAddNewButton('toolbar-header-right'));
			$membersGridfield->getComponentByType('GridFieldDataColumns')->setDisplayFields(array ('FirstName'=>'FirstName','Surname'=>'Surname','Email'=>'Email', 'Approved' => 'Approved'));
			$membersGridfield->addComponent(new GridField_ApproveUserAction());
			
			$config = GridFieldConfig_RelationEditor::create();
			$fields->addFieldToTab('Root.Forum', CheckboxField::create('UserModerationRequired', 'Do Users Require Approval?'));
			$fields->addFieldToTab('Root.Forum', GridField::create('Moderators', 'Moderators', $this->owner->Moderators(), $config));

		}
	}
	
	
}