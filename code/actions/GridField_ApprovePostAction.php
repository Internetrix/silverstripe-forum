<?php

class GridField_ApprovePostAction implements GridField_ColumnProvider, GridField_ActionProvider {

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
				'ApprovePost'.$record->ID,
				'Approve Post',
				"doapprovepost",
				array('RecordID' => $record->ID)
		);
		
		if($record->Status == 'Awaiting') {
			return $field->Field();
		}	
	}

	public function getActions($gridField) {
		return array('doapprovepost');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'doapprovepost') {
			$postID = $arguments['RecordID'];
			
			$post = Post::get()->byID($postID);
			
			if($post && $post->Status == "Awaiting") {
				$post->Status = "Moderated";
				$post->write();
			}
			

			// output a success message to the user
			Controller::curr()->getResponse()->setStatusCode(
			200,
			'Post has been approved.'
			);
		}
	}
}