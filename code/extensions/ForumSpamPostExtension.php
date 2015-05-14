<?php
class ForumSpamPostExtension extends DataExtension {

	public function augmentSQL(SQLQuery &$query) {
		if (Config::inst()->forClass('Post')->allow_reading_spam) return;

		$member = Member::currentUser();
		$forum = $this->owner->Forum();

		// Do Status filtering

		Debug::show($this->owner);
		if(!$this->owner->ID) {
			return;
		}
		Debug::show($forum->canModerate());

		if($member && is_numeric($forum->ID) && $forum->canModerate()) {
			Debug::show("here ".$this->owner->Title);
			$filter = "\"Post\".\"Status\" IN ('Moderated', 'Awaiting')";
		} else {
			Debug::show($this->owner);
			
			$filter = "\"Post\".\"Status\" = 'Moderated'";
		}

		$query->addWhere($filter);

		// Filter out posts where the author is in some sort of banned / suspended status

		$query->addInnerJoin("Member", "\"AuthorStatusCheck\".\"ID\" = \"Post\".\"AuthorID\"", "AuthorStatusCheck");

		$authorStatusFilter = '"AuthorStatusCheck"."ForumStatus" = \'Normal\'';
		if ($member && $member->ForumStatus == 'Ghost') $authorStatusFilter .= ' OR "Post"."AuthorID" = '. $member->ID;

		$query->addWhere($authorStatusFilter);
		$query->setDistinct(false);
	}

}
