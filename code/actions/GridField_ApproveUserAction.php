<?php

class GridField_ApproveUserAction implements GridField_ColumnProvider, GridField_ActionProvider {

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
				'ApproveUser'.$record->ID,
				'Approve User',
				"doapproveuser",
				array('RecordID' => $record->ID)
		);
		
		if(!$record->Approved) {
			return $field->Field();
		}	
	}

	public function getActions($gridField) {
		return array('doapproveuser');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'doapproveuser') {
			$userid = $arguments['RecordID'];
			$groupID = $data['ID'];
			
			$group = Group::get('Group', 'IsForumGroup = 1')->byID($groupID);
			$members  = $group->Members();
			$newMember  = $members->byID($userid);
			$members->add($newMember, array('Approved' => 1));
			
			// Send an email to the user, notifying them of approval
			$forums = Forum::get("Forum");
			
			foreach($forums as $forum) {
				// Check to see if this group is for the forum
				$forumGroups = $forum->PosterGroups();
				$checkForGroup = $forumGroups->byID($groupID);

				if($checkForGroup && $checkForGroup->UserModerationRequired) {
					// Send an email to the user
					$this->sendEmail($newMember, $forum);
				}
			}

			// output a success message to the user
			Controller::curr()->getResponse()->setStatusCode(
			200,
			'Member has been approved.'
			);
		}
	}
	
	public function sendEmail($member, $forum) {
		if($member) {
			$adminEmail = Config::inst()->get('Forum', 'send_email_from');
				
			$email = new Email();
			$email->setFrom($adminEmail);
			$email->setTo($member->Email);
			$email->setSubject($forum->Title.": Registration Approved");
			$email->setTemplate('ForumRegistration_NotifyUserApproved');
			$email->populateTemplate(new ArrayData(array(
					'NewUser' => $member,
					'Forum' => $forum
			)));
				
			$email->send();
		}
	}
}