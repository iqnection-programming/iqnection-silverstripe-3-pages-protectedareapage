<?php


namespace IQnection\ProtectedArea\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\ORM\ArrayList;
use IQnection\ProtectedArea\ProtectedAreaPage\ProtectedAreaPage;

class ProtectedAreaUserGroup extends DataObject
{
	private static $db = [
		'Title' => 'Varchar(255)'
	];
	
	private static $many_many = [
		'Pages' => \Page::class
	];

	private static $belongs_many_many = [
		'ProtectedAreaUsers' => ProtectedAreaUser::class,
	];
	
	private static $summary_fields = [
		'Title' => 'Title'
	];

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		if ($this->ID)
		{
			$usersGridField = $fields->dataFieldByName('ProtectedAreaUsers');
			$pagesGridField = $fields->dataFieldByName('Pages');
			$fields->removeByName('ProtectedAreaUsers');
			$fields->removeByName('Pages');
			$fields->addFieldToTab('Root.Main', Forms\TabSet::create('MainTabs'));
			$fields->addFieldToTab('Root.Main.MainTabs.Users', $usersGridField );
			$fields->addFieldToTab('Root.Main.MainTabs.Pages', $pagesGridField );
			$pagesGridField->getConfig()->removeComponentsByType('GridFieldAddNewButton');
			$fields->addFieldToTab('Root.Main.MainTabs.Pages', Forms\CheckboxSetField::create('Pages','Allowed Pages')
				->addExtraClass('vertical')
				->setSource($this->PageSelectionOptions()) );
		}
		else
		{
			$fields->addFieldToTab('Root.Main', Forms\HeaderField::create('note','You must save before adding users and/or pages',2) );
		}
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}

	public function canCreate($member = null, $context = []) { return true; }
	public function canDelete($member = null, $context = []) { return true; }
	public function canEdit($member = null, $context = [])   { return true; }
	public function canView($member = null, $context = [])   { return true; }

	public function PageSelectionOptions()
	{
		$list = array();
		foreach(ProtectedAreaPage::get() as $ProtectedArea)
		{
			if ($ProtectedArea->ClassName != ProtectedAreaPage::class)
			{
				$list[$ProtectedPage->ID] = $ProtectedArea->Breadcrumbs(20,true,ProtectedAreaPage::class,true,'/');
			}
			$this->addChildrenToOptionList($ProtectedArea,$list);
		}
		return $list;
	}
	
	protected function addChildrenToOptionList($Parent,&$list)
	{
		if ($Parent->AllChildren()->Count())
		{
			foreach($Parent->AllChildren() as $child)
			{
				$list[$child->ID] = $child->Breadcrumbs(20,true,ProtectedAreaPage::class,true);
				$this->addChildrenToOptionList($child,$list);
			}
		}
	}
	
	public function validate()
	{
		$result = parent::validate();
		if (!$this->Title)
		{
			$result->addError('Please provide a title');
		}
		elseif (self::get()->exclude('ID',$this->ID)->find('Title',$this->Title))
		{
			$result->addError('Title must be unique');
		}
		return $result;
	}
	
	public function getBetterButtonsActions()
	{
		$actions = parent::getBetterButtonsActions();
		if (!$this->ID)
		{
			$actions->removeByName('action_doSaveAndQuit');
			$actions->removeByName('action_doSaveAndAdd');
		}
		return $actions;
	}
	
	public function getAllProtectedPages()
	{
		$protectedPages = new ArrayList();
		foreach(ProtectedAreaPage::get() as $protectedArea)
		{
			$protectedPages->merge($protectedArea->ProtectedPages());
		}
		return $protectedPages;
	}
}









