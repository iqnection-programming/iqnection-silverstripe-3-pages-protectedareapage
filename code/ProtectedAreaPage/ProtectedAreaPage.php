<?php

use SilverStripe\Forms;
use SilverStripe\ORM;

class ProtectedAreaPage extends Page
{
	private static $icon = 'iq-protectedareapage/images/secure-area-page.png';
	
	private static $defaults = [
		'ShowInMenus' => false,
		'ShowInSearch' => false
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Help', Forms\LiteralField::create('help',file_get_contents(PROTECTED_AREA_PAGE_ROOT.'/instructions.html')) );
		if (!ProtectedAreaUserGroup::get()->Count())
		{
			$fields->addFieldToTab('Root.Users', Forms\HeaderField::create('Users','You must create User Groups before creating users',2) );
		}
		else
		{
			$fields->addFieldToTab('Root.Users', Forms\GridField\GridField::create(
				'ProtectedAreaUsers',
				'Users',
				ProtectedAreaUser::get(),
				Forms\GridField\GridFieldConfig_RelationEditor::create()
			));
		}
		$fields->addFieldToTab('Root.User Groups', Forms\GridField\GridField::create(
			'ProtectedAreaUserGroups',
			'User Groups',
			ProtectedAreaUserGroup::get(),
			Forms\GridField\GridFieldConfig_RelationEditor::create()
		));
		return $fields;
	}
	
	public function ProtectedPages()
	{
		$ProtectedPages = new ORM\ArrayList();
		$this->getChildProtectedPages($this,$ProtectedPages);
		return $ProtectedPages;
	}
	
	public function getChildProtectedPages($Parent,&$list)
	{
		foreach($Parent->Children() as $child)
		{
			$list->push( $child );
			$this->getChildProtectedPages($child,$list);
		}
	}
}










