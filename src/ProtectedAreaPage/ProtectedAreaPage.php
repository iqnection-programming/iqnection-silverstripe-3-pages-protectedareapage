<?php

namespace IQnection\ProtectedArea\ProtectedAreaPage;

use SilverStripe\Forms;
use SilverStripe\ORM\ArrayList;
use IQnection\ProtectedArea\Model\ProtectedAreaUserGroup;
use IQnection\ProtectedArea\Model\ProtectedAreaUser;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class ProtectedAreaPage extends \Page
{
	private static $icon = 'iqnection-pages/protectedareapage:images/secure-area-page.png';
	
	private static $defaults = [
		'ShowInMenus' => false,
		'ShowInSearch' => false
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Help', Forms\LiteralField::create('help',file_get_contents(ModuleResourceLoader::singleton()->resolveURL('iqnection-pages/protectedareapage:docs/instructions.html'))) );
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
		$ProtectedPages = ArrayList::create();
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










