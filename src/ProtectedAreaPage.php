<?php

namespace IQnection\ProtectedArea;

use SilverStripe\Forms;
use SilverStripe\ORM\ArrayList;
use IQnection\ProtectedArea\Model\ProtectedAreaUserGroup;
use IQnection\ProtectedArea\Model\ProtectedAreaUser;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class ProtectedAreaPage extends \Page
{
	private static $table_name = 'ProtectedAreaPage';
	
	private static $icon = 'iqnection-pages/protectedareapage:images/secure-area-page.png';
	
	private static $defaults = [
		'ShowInMenus' => false,
		'ShowInSearch' => false
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Help', Forms\LiteralField::create('help',file_get_contents(realpath(__DIR__.'/../docs/instructions.html'))) );
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
		
		$this->extend('updateCMSFields', $fields);
		
		$fields->removeByName('AccessControl');
		
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










