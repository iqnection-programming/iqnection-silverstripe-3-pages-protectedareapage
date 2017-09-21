<?php


class ProtectedAreaUserGroup extends DataObject
{
	private static $db = array(
		'Title' => 'Varchar(255)'
	);
	
	private static $many_many = array(
		'Pages' => 'Page'
	);

	private static $belongs_many_many = array(
		'ProtectedAreaUsers' => 'ProtectedAreaUser',
	);
	
	private static $summary_fields = array(
		'Title' => 'Title'
	);

	function getCMSFields()
	{
		$fields = parent::getCMSFields();
		if ($this->ID)
		{
			$usersGridField = $fields->dataFieldByName('ProtectedAreaUsers');
			$pagesGridField = $fields->dataFieldByName('Pages');
			$fields->removeByName('ProtectedAreaUsers');
			$fields->removeByName('Pages');
			$fields->addFieldToTab('Root.Main', TabSet::create('MainTabs'));
			$fields->addFieldToTab('Root.Main.MainTabs.Users', $usersGridField );
			$fields->addFieldToTab('Root.Main.MainTabs.Pages', $pagesGridField );
			$pagesGridField->getConfig()->removeComponentsByType('GridFieldAddNewButton');
//			$pagesGridField->getConfig()->getComponentByType('GridFieldAddExistingAutocompleter')
//				->setSearchList(Page::get()->filter('ID',$this->getAllProtectedPages()->column('ID')));
			$fields->addFieldToTab('Root.Main.MainTabs.Pages', CheckboxSetField::create('Pages','Allowed Pages')
				->addExtraClass('vertical')
				->setSource($this->PageSelectionOptions()) );
		}
		else
		{
			$fields->addFieldToTab('Root.Main', HeaderField::create('note','You must save before adding users and/or pages',2) );
		}
		return $fields;
	}

	public function canCreate($member = null) { return true; }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }

	public function PageSelectionOptions()
	{
		$list = array();
		foreach(ProtectedAreaPage::get() as $ProtectedArea)
		{
			$list[$ProtectedPage->ID] = $ProtectedArea->Breadcrumbs(20,true,'ProtectedAreaPage',true);
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
				$list[$child->ID] = $child->Breadcrumbs(20,true,'ProtectedAreaPage',true);
				$this->addChildrenToOptionList($child,$list);
			}
		}
	}
	
	public function validate()
	{
		$result = parent::validate();
		if (!$this->Title)
		{
			$result->error('Please provide a title');
		}
		elseif (self::get()->exclude('ID',$this->ID)->find('Title',$this->Title))
		{
			$result->error('Title must be unique');
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









