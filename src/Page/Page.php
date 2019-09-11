<?php


namespace IQnection\ProtectedArea\Page;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms;
use IQnection\ProtectedArea\Model\ProtectedAreaUserGroup;
use IQnection\ProtectedArea\Model\ProtectedAreaUser;
use IQnection\ProtectedArea\ProtectedAreaPage;

class Page extends DataExtension
{	
	private static $belongs_many_many = [
		'ProtectedAreaUserGroups' => ProtectedAreaUserGroup::class
	];
	
	public function updateCMSFields(Forms\FieldList $fields)
	{
		if ($this->isInProtectedArea())
		{
			$fields->addFieldToTab('Root.AccessControl', Forms\LiteralField::create('access-note','<p>Uncheck all selections to inherit parent page permissions</p>') );
			$fields->addFieldToTAb('Root.AccessControl', Forms\CheckboxSetField::create('ProtectedAreaUserGroups','Allowed User Groups')
				->setValue($this->owner->ProtectedAreaUserGroups())
				->setSource(ProtectedAreaUserGroup::get()->map('ID','Title')) );
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
		// if no groups are selected, then we inherit the parent
		if (!$this->owner->ProtectedAreaUserGroups()->Count())
		{
			if ( ($parent = $this->owner->Parent()) && ($parent->Exists()) )
			{
				if ($parent->isInProtectedArea())
				{
					return $parent->CanGroupViewProtectedContent($ProtectedUserGroupID);
				}
			}
			return false;
		}
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
		if ($this->owner->ClassName == ProtectedAreaPage::class)
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















