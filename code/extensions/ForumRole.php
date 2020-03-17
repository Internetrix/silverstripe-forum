<?php
/**
 * ForumRole
 *
 * This decorator adds the needed fields and methods to the {@link Member}
 * object.
 *
 * @package forum
 */
class ForumRole extends DataExtension {

	/**
	 * Edit the given query object to support queries for this extension
	 */
	function augmentSQL(SQLQuery &$query) {}


	/**
	 * Update the database schema as required by this extension
	 */
	function augmentDatabase() {
		$exist = DB::tableList();
 		if(!empty($exist) && array_search('ForumMember', $exist) !== false) {
			DB::query( "UPDATE \"Member\", \"ForumMember\" " .
				"SET \"Member\".\"ClassName\" = 'Member'," .
				"\"Member\".\"ForumRank\" = \"ForumMember\".\"ForumRank\"," .
				"\"Member\".\"Occupation\" = \"ForumMember\".\"Occupation\"," .
				"\"Member\".\"Country\" = \"ForumMember\".\"Country\"," .
				"\"Member\".\"Nickname\" = \"ForumMember\".\"Nickname\"," .
				"\"Member\".\"FirstNamePublic\" = \"ForumMember\".\"FirstNamePublic\"," .
				"\"Member\".\"SurnamePublic\" = \"ForumMember\".\"SurnamePublic\"," .
				"\"Member\".\"OccupationPublic\" = \"ForumMember\".\"OccupationPublic\"," .
				"\"Member\".\"CountryPublic\" = \"ForumMember\".\"CountryPublic\"," .
				"\"Member\".\"EmailPublic\" = \"ForumMember\".\"EmailPublic\"," .
				"\"Member\".\"AvatarID\" = \"ForumMember\".\"AvatarID\"," .
				"\"Member\".\"LastViewed\" = \"ForumMember\".\"LastViewed\"" .
				"WHERE \"Member\".\"ID\" = \"ForumMember\".\"ID\""
			);
			echo("<div style=\"padding:5px; color:white; background-color:blue;\">" . _t('ForumRole.TRANSFERSUCCEEDED','The data transfer has succeeded. However, to complete it, you must delete the ForumMember table. To do this, execute the query \"DROP TABLE \'ForumMember\'\".') . "</div>" );
		}
	}

	private static $db =  array(
		'ForumRank' => 'Varchar',
		'Occupation' => 'Varchar',
		'Company' => 'Varchar',
		'City' => 'Varchar',
		'Country' => 'Varchar',
		'Nickname' => 'Varchar',
		'FirstNamePublic' => 'Boolean',
		'SurnamePublic' => 'Boolean',
		'OccupationPublic' => 'Boolean',
		'CompanyPublic' => 'Boolean',
		'CityPublic' => 'Boolean',
		'CountryPublic' => 'Boolean',
		'EmailPublic' => 'Boolean',
		'LastViewed' => 'SS_Datetime',
		'Signature' => 'Text',
		'ForumStatus' => 'Enum("Normal, Banned, Ghost", "Normal")',
		'SuspendedUntil' => 'Date'
	);

	private static $has_one = array(
		'Avatar' => 'Image'
	);

	private static $has_many = array(
		'ForumPosts' => 'Post'
	);

	private static $defaults = array(
		'ForumRank' => 'Community Member'
	);

	private static $searchable_fields = array(
		'Nickname' => true
	);

	private static $indexes = array(
		'Nickname' => true
	);

	private static $field_labels = array(
		'SuspendedUntil' => "Suspend this member from writing on forums until the specified date"
	);

	public function onBeforeDelete() {
		parent::onBeforeDelete();

		$avatar = $this->owner->Avatar();
		if($avatar && $avatar->exists()) {
			$avatar->delete();
		}

		$forumGroups = $this->owner->Groups()->filter('IsForumGroup', '1');
		if($forumGroups && $forumGroups->Count()){
			$this->owner->hasForumGroup = true;
		}
	}

	public function onAfterDelete(){
		parent::onAfterDelete();

		if($this->owner->hasForumGroup === true){
			$adminEmail = Config::inst()->get('Forum', 'send_email_from');

			$email = new Email();
			$email->setFrom($adminEmail);
			$email->setTo($this->owner->Email);
			$email->setSubject('Account Deleted');
			$email->setTemplate('AccountDeletion_ForgotNicknameEmail');
			$email->populateTemplate(new ArrayData(array(
				'Member' => $this->owner
			)));

			$email->send();
		}
	}

	function ForumRank() {
		$groups = Group::get("Group", "IsForumGroup = 1");

		$moderatedForums = false;

		foreach($groups as $group) {
			if($group->Moderators() && $group->Moderators()->byID($this->owner->ID)) {
				$moderatedForums = true;
			}
		}

		if($moderatedForums) return _t('MODERATOR','Forum Moderator');
		else return $this->owner->getField('ForumRank');
	}

	function isFirstNamePublic() {
		return $this->owner->FirstNamePublic || Permission::check('ADMIN');
	}
	function isSurnamePublic() {
		return $this->owner->SurnamePublic || Permission::check('ADMIN');
	}
	function isOccupationPublic() {
		return $this->owner->OccupationPublic || Permission::check('ADMIN');
	}
	function isCompanyPublic() {
		return $this->owner->CompanyPublic || Permission::check('ADMIN');
	}
	function isCityPublic() {
		return $this->owner->CityPublic || Permission::check('ADMIN');
	}
	function isCountryPublic() {
		return $this->owner->CountryPublic || Permission::check('ADMIN');
	}
	function isEmailPublic() {
		return $this->owner->EmailPublic || Permission::check('ADMIN');
	}
	/**
	 * Run the Country code through a converter to get the proper Country Name
	 */
	function FullCountry() {
		$locale = new Zend_Locale();
		$locale->setLocale($this->owner->Country);
		return $locale->getRegion();
	}
	function NumPosts() {
		if(is_numeric($this->owner->ID)) {
			return $this->owner->ForumPosts()->Count();
		} else {
			return 0;
		}
	}

	function Link() {
		return "ForumMemberProfile/show/" . $this->owner->ID;
	}


	/**
	 * Get the fields needed by the forum module
	 *
	 * @param bool $showIdentityURL Should a field for an OpenID or an i-name
	 *                              be shown (always read-only)?
	 * @return FieldList Returns a FieldList containing all needed fields for
	 *                  the registration of new users
	 */
	function getForumFields($showIdentityURL = false, $addmode = false) {
		$gravatarText = (DataObject::get_one("ForumHolder", "\"AllowGravatars\" = 1")) ? '<small>'. _t('ForumRole.CANGRAVATAR', 'If you use Gravatars then leave this blank') .'</small>' : "";

		//Sets the upload folder to the Configurable one set via the ForumHolder or overridden via Config::inst()->update().
		$avatarField = new FileField('Avatar', _t('ForumRole.AVATAR','Avatar Image') .' '. $gravatarText);
		$avatarField->setFolderName(Config::inst()->get('ForumHolder','avatars_folder'));
		$avatarField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

		$personalDetailsFields = new CompositeField(
			new HeaderField("PersonalDetails", "GPH Forum - ".($addmode ? "New User Setup" : "User Details")),

			new LiteralField("Blurb","<p id=\"helpful\">" . _t('ForumRole.TICK', 'Tick the fields to show in public profile') . "</p>"),

			new TextField("Nickname", _t('ForumRole.NICKNAME','Nickname')),
			new CheckableOption("FirstNamePublic", new TextField("FirstName", _t('ForumRole.FIRSTNAME','First name'))),
			new CheckableOption("SurnamePublic", new TextField("Surname", _t('ForumRole.SURNAME','Surname'))),
			new CheckableOption("OccupationPublic", new TextField("Occupation", _t('ForumRole.OCCUPATION','Occupation')), true),
			new CheckableOption('CompanyPublic', new TextField('Company', _t('ForumRole.COMPANY', 'Company')), true),
			new CheckableOption('CityPublic', new TextField('City', _t('ForumRole.CITY', 'City')), true),
			new CheckableOption("CountryPublic", new ForumCountryDropdownField("Country", _t('ForumRole.COUNTRY','Country')), true),
			new CheckableOption("EmailPublic", new EmailField("Email", _t('ForumRole.EMAIL','Email'))),
			new ConfirmedPasswordField("Password", _t('ForumRole.PASSWORD','Password'))
		);

		// Get Forum Groups
		$forumGroups = DataObject::get_one("ForumHolder")->RegGroups()->map()->toArray();

		$personalDetailsFields->push(LiteralField::create('GroupsExp', 'Please select which forums you\'d like to sign up to. Note that all forums will require approval from a moderator'));

		if(count($forumGroups) > 1) {
			$groupField = CheckboxSetField::create('ForumGroups', 'Access to Forums', $forumGroups);

			$personalDetailsFields->push($groupField);

			// Get the users forum groups and preselect the ones, but only do it when not in registration
			if(!$addmode) {
				$memberGroups = Member::currentUser()->Groups()->filter('IsForumGroup' , true)->column('ID'); // Only gets the IDs
				$groupField->setValue($memberGroups); // Set the value

				$emailLinkBodyMessage = 'Making changes to your forums may require approval from moderators. Removing yourself from a forum is instantaneous.';

				$this->owner->extend('updateForumFieldsGroupsExp', $emailLinkBodyMessage);

				$personalDetailsFields->push(LiteralField::create('GroupsExp', $emailLinkBodyMessage));
			}
		}

		$personalDetailsFields->push($avatarField);

		$personalDetailsFields->setID('PersonalDetailsFields');

		$fieldset = new FieldList(
			$personalDetailsFields
		);

		if($showIdentityURL) {
			$fieldset->insertBefore(
				new ReadonlyField('IdentityURL', _t('ForumRole.OPENIDINAME','OpenID/i-name')),
				'Password'
			);
			$fieldset->insertAfter(
				new LiteralField(
					'PasswordOptionalMessage',
					'<p>' . _t('ForumRole.PASSOPTMESSAGE','Since you provided an OpenID respectively an i-name the password is optional. If you enter one, you will be able to log in also with your e-mail address.') . '</p>'
				),
				'IdentityURL'
			);
		}

		if($this->owner->IsSuspended()) {
			$fieldset->insertAfter(
				new LiteralField(
					'SuspensionNote',
					'<p class="message warning suspensionWarning">' . $this->ForumSuspensionMessage() . '</p>'
				),
				'Blurb'
			);
		}

		$this->owner->extend('updateForumFields', $fieldset);

		return $fieldset;
	}

	/**
	 * Get the fields needed by the forum module
	 *
	 * @param bool $needPassword Should a password be required?
	 * @return Validator Returns a Validator for the fields required for the
	 * 								registration of new users
	 */
	function getForumValidator($needPassword = true) {
		if ($needPassword) {
			$validator = new RequiredFields("Nickname", "Email", "ForumGroups", "Password");
		} else {
			$validator = new RequiredFields("Nickname", "Email", "ForumGroups");
		}
		$this->owner->extend('updateForumValidator', $validator);

		return $validator;
	}

	function updateCMSFields(FieldList $fields) {
		$allForums = DataObject::get('Forum');
		$suspend = $fields->dataFieldByName('SuspendedUntil');
		$suspend->setConfig('showcalendar', true);
		if(Permission::checkMember($this->owner->ID, "ACCESS_FORUM")) {
			$avatarField = new FileField('Avatar', _t('ForumRole.UPLOADAVATAR', 'Upload avatar'));
			$avatarField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

			$fields->addFieldToTab('Root.Forum', $avatarField);
			$fields->addFieldToTab('Root.Forum',new DropdownField("ForumRank", _t('ForumRole.FORUMRANK', "User rating"), array(
				"Community Member" => _t('ForumRole.COMMEMBER'),
				"Administrator" => _t('ForumRole.ADMIN','Administrator'),
				"Moderator" => _t('ForumRole.MOD','Moderator')
			)));
			$fields->addFieldToTab('Root.Forum', $this->owner->dbObject('ForumStatus')->scaffoldFormField());
		}
	}

	public function IsSuspended() {
		if($this->owner->SuspendedUntil) {
			return strtotime(SS_Datetime::now()->Format('Y-m-d')) < strtotime($this->owner->SuspendedUntil);
		} else {
			return false;
		}
	}

	public function IsBanned() {
		return $this->owner->ForumStatus == 'Banned';
	}

	public function IsGhost() {
		return $this->owner->ForumStatus == 'Ghost' && $this->owner->ID !== Member::currentUserID();
	}

	/**
	 * Can the current user edit the given member?
	 *
	 * @return true if this member can be edited, false otherwise
	 */
	function canEdit($member = null) {
		if(!$member) $member = Member::currentUser();

		if($this->owner->ID == Member::currentUserID()) return true;

		if($member) return $member->can('AdminCMS');

		return false;
	}


	/**
	 * Used in preference to the Nickname field on templates
	 *
	 * Provides a default for the nickname field (first name, or "Anonymous
	 * User" if that's not set)
	 */
	function Nickname() {
		if($this->owner->Nickname) return $this->owner->Nickname;
		elseif($this->owner->FirstNamePublic && $this->owner->FirstName) return $this->owner->FirstName;
		else return _t('ForumRole.ANONYMOUS','Anonymous user');
	}

	/**
	 * Return the url of the avatar or gravatar of the selected user.
	 * Checks to see if the current user has an avatar, if they do use it
	 * otherwise query gravatar.com
	 *
	 * @return String
	 */
	function getFormattedAvatar() {
		$default = "forum/images/forummember_holder.gif";
		$currentTheme = Config::inst()->get('SSViewer', 'theme');

		if(file_exists('themes/' . $currentTheme . '_forum/images/forummember_holder.gif')) {
			$default = 'themes/' . $currentTheme . '_forum/images/forummember_holder.gif';
		}
		// if they have uploaded an image
		if($this->owner->AvatarID) {
			$avatar = Image::get()->byID($this->owner->AvatarID);
			if(!$avatar) return $default;

			$resizedAvatar = $avatar->SetWidth(80);
			if(!$resizedAvatar) return $default;

			return $resizedAvatar->URL;
		}

		//If Gravatar is enabled, allow the selection of the type of default Gravatar.
		if($holder = ForumHolder::get()->filter('AllowGravatars',1)->first()) {
			// If the GravatarType is one of the special types, then set it otherwise use the
			//default image from above forummember_holder.gif
			if($holder->GravatarType){
 				$default = $holder->GravatarType;
 			} else {
 				// we need to get the absolute path for the default forum image
 				return $default;
 			}
			// ok. no image but can we find a gravatar. Will return the default image as defined above if not.
			return "http://www.gravatar.com/avatar/".md5($this->owner->Email)."?default=".urlencode($default)."&amp;size=80";
		}

		return $default;
	}

	/**
	 * Conditionally includes admin email address (hence we can't simply generate this
	 * message in templates). We don't need to spam protect the email address as
	 * the note only shows to logged-in users.
	 *
	 * @return String
	 */
	function ForumSuspensionMessage() {
		$msg = _t('ForumRole.SUSPENSIONNOTE', 'This forum account has been suspended.');
		$adminEmail = Config::inst()->get('Email', 'admin_email');

		if($adminEmail) {
			$msg .= ' ' . sprintf(
				_t('ForumRole.SUSPENSIONEMAILNOTE', 'Please contact %s to resolve this issue.'),
				$adminEmail
			);
		}
		return $msg;
	}
}



/**
 * ForumRole_Validator
 *
 * This class is used to validate the new fields added by the
 * {@link ForumRole} decorator in the CMS backend.
 */
class ForumRole_Validator extends Extension {

	/**
	 * Client-side validation code
	 *
	 * @param string $js The javascript validation code
	 * @return string Returns the needed javascript code for client-side
	 *                validation.
	 */
	function updateJavascript(&$js, &$form) {

		$formID = $form->FormName();
		$passwordFieldName = $form->dataFieldByName('Password')->id();

		$passwordConfirmField = $form->dataFieldByName('ConfirmPassword');
		if(!$passwordConfirmField) return;

		$passwordConfirmFieldName = $passwordConfirmField->id();

		$passwordcheck = <<<JS
Behaviour.register({
	"#$formID": {
		validatePasswordConfirmation: function() {
			var passEl = _CURRENT_FORM.elements['Password'];
			var confEl = _CURRENT_FORM.elements['ConfirmPassword'];

			if(passEl.value == confEl.value) {
			  clearErrorMessage(confEl.parentNode);
				return true;
			} else {
				validationError(confEl, "Passwords don't match.", "error");
				return false;
			}
		},
		initialize: function() {
			var passEl = $('$passwordFieldName');
			var confEl = $('$passwordConfirmFieldName');

			confEl.value = passEl.value;
		}
	}
});
JS;
		Requirements::customScript($passwordcheck,
															 'func_validatePasswordConfirmation');

		$js .= "\$('$formID').validatePasswordConfirmation();";
		return $js;
	}
}
