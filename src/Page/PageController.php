<?php


namespace IQnection\ProtectedArea\Page;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Security;

class PageController extends Extension
{
	private static $allowed_actions = [
		'permission_error'
	];
	
	public function beforeCallActionHandler(&$request,&$action)
	{
		if (Security::getCurrentUser()) { return; }
		if (!$this->owner->UserCanViewPage())
		{
			$action = $this->owner->request->param('Action');
			if (!$User = $this->owner->ProtectedAreaUser())
			{
				// don't redirect if we're requesting the login page or the login form
				$allowedActions = array(
					'login',
					'logout',
					'ProtectedAreaUserLoginForm',
					'reset_password',
					'ResetProtectedAreaUserPasswordForm'
				);
				if ( (!in_array($action,$allowedActions)) && ($page = $this->owner->ProtectiveParent()) )
				{
					return $this->owner->redirect($page->Link('login?BackURL='.urlencode($this->owner->request->getURL(true))));
				}
			}
			else
			{// user is logged in
				if ($this->owner->ProtectiveParent()->ID == $this->owner->ID)
				{
					$allowedActions = array(
						'logout',
						'reset_password',
						'ResetProtectedAreaUserPasswordForm'
					);
					if (!in_array($action,$allowedActions))
					{
						$action = 'permission_error';
						return $this->permission_error();
					}
				}
				else
				{
					$action = 'permission_error';
					return $this->permission_error();
				}				
			}
		}
	}	
	
	public function permission_error()
	{
		return $this->owner->renderWith(array('ProtectedAreaPage_permission_error','MinisitePage','Page'));
	}
}
