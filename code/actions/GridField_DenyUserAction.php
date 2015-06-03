<?php

class GridField_DenyUserAction implements GridField_ColumnProvider, GridField_ActionProvider {

	public function augmentColumns($gridField, &$columns) {
		if(!in_array('Actions', $columns)) {
			$columns[] = 'Actions';
		}
	}

	public function getColumnAttributes($gridField, $record, $columnName) {
		return array('class' => 'col-buttons');
	}

	public function getColumnMetadata($gridField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}

	public function getColumnsHandled($gridField) {
		return array('Actions');
	}

	public function getColumnContent($gridField, $record, $columnName) {
		if(!$record->canEdit()) return;

		$field = GridField_FormAction::create(
				$gridField,
				'DenyUser'.$record->ID,
				'Deny User',
				"dodenyuser",
				array('RecordID' => $record->ID)
		);
		
		if(!$record->Approved) {
			return $field->Field();
		}	
	}

	public function getActions($gridField) {
		return array('dodenyuser');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'dodenyuser') {
			$userid = $arguments['RecordID'];
			$groupID = $data['ID'];
			
			$group = Group::get('Group', 'IsForumGroup = 1')->byID($groupID);
			$members  = $group->Members();
			$newMember  = $members->byID($userid);
			$members->remove($newMember);
			
			$adminEmail = Config::inst()->get('Forum', 'send_email_from');
				
			$email = new Email();
			$email->setFrom($adminEmail);
			$email->setTo($newMember->Email);
			$email->setSubject($this->Title.": Registration Denied");
			$email->setTemplate('ForumRegistration_NotifyUserDeclined');
			$email->populateTemplate(new ArrayData(array(
					'NewUser' => $newMember,
					'Forum' => $group
			)));
				
			$email->send();

			// output a success message to the user
			Controller::curr()->getResponse()->setStatusCode(
			200,
			'Member has been denied.'
			);
		}
	}
}