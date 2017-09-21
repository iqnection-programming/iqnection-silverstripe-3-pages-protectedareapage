<?php



class ProtectedArea_Page_Extension extends DataExtension
{	
	private static $belongs_many_many = array(
		'ProtectedAreaUserGroups' => 'ProtectedAreaUserGroup'
	);
	
	public function updateCMSFields(&$fields)
	{
		if ($this->isInProtectedArea())
		{
			$fields->addFieldToTab('Root.AccessControl', GridField::create(
				'ProtectedAreaUserGroups',
				'Allowed User Groups',
				$this->owner->ProtectedAreaUserGroups(),
				$ppug_gfConfig = GridFieldConfig_RelationEditor::create()
			));
			$ppug_gfConfig->removeComponentsByType('GridFieldAddNewButton');
		}
		else
		{
			$fields->removeByName('ProtectedAreaUserGroups');
		}
		return $fields;
	}
		
	public function CanGroupViewProtectedContent($ProtectedUserGroupID)
	{
		if (is_object($ProtectedUserGroupID)) { $ProtectedUserGroupID = $ProtectedUserGroupID->ID; }
		// is this page directly accessible by the group
		return ($this->owner->ProtectedAreaUserGroups()->byID($ProtectedUserGroupID));
	}
	
	public function isInProtectedArea()
	{
		return (bool) $this->owner->ProtectiveParent();
	}
	
	public function ProtectiveParent()
	{
		// is this the top level protected page
		if ($this->owner->ClassName == 'ProtectedAreaPage')
		{
			return $this->owner;
		}
		if ($this->owner->Parent()->Exists())
		{
			return $this->owner->Parent()->ProtectiveParent();
		}
		return false;
	}
	
	public function ProtectedAreaUser()
	{
		return ProtectedAreaUser::CurrentSiteUser();
	}
	
	public function UserCanViewPage()
	{
		$allowed = true;
		if ($this->owner->isInProtectedArea())
		{
			// see if we're allowed access to any child pages
			$allowed = $this->owner->AllowedProtectedAccess($this->owner,$this->ProtectedAreaUser());
		}
		return $allowed;
	}
	
	public function AllowedProtectedAccess($Page,$protectedUser)
	{
		if (!$protectedUser) { return false; }
		// check if the user has access to the current page
		foreach($protectedUser->ProtectedAreaUserGroups() as $group)
		{
			if ($Page->CanGroupViewProtectedContent($group))
			{
				return true;
			}
		}
		// check if the user has access to any child pages
		foreach($Page->Children() as $child)
		{
			if ($child->AllowedProtectedAccess($child,$protectedUser))
			{
				return true;
			}
		}
		return false;
	}

}

class ProtectedArea_Page_Controller_Extension extends Extension
{
	private static $allowed_actions = array(
		'permission_error'
	);
	
	public function beforeCallActionHandler(&$request,&$action)
	{
		if (Member::CurrentUser()) { return; }
		if (!$this->owner->UserCanViewPage())
		{
			if (!$User = $this->owner->ProtectedAreaUser())
			{
				// don't redirect if we're requesting the login page or the login form
				$action = $this->owner->request->param('Action');
				$allowedActions = array(
					'login',
					'ProtectedAreaUserLoginForm',
					'reset_password',
					'ResetProtectedAreaUserPasswordForm'
				);
				if (!in_array($action,$allowedActions))
				{
					return $this->owner->redirect($this->owner->ProtectiveParent()->Link('login?BackURL='.urlencode($this->owner->request->requestVar('url'))));
				}
			}
			else
			{// user is logged in
				$action = 'permission_error';
			}
		}
	}	
	
	public function permission_error()
	{
		return $this->owner->renderWith(array('ProtectedAreaPage_permission_error','MinisitePage','Page'));
	}
}














