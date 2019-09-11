<?php

namespace IQnection\ProtectedArea;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms;
use IQnection\FormUtilities\FormUtilities;
use IQnection\ProtectedArea\Model\ProtectedAreaUser;

class ProtectedAreaPageController extends \PageController
{
	private static $allowed_actions = [
		'login',
		'ProtectedAreaUserLoginForm',
		'reset_password',
		'ResetProtectedAreaUserPasswordForm',
		'change_password',
		'ChangeProtectedAreaUserPasswordForm',
		'logout',
	];
	
	public function ProtectedAreaUserConfig($var)
	{
		return Config::inst()->get(ProtectedAreaUser::class,$var);
	}
		
	public function index()
	{
		if (!$this->ProtectedAreaUser())
		{
			return $this->redirect($this->Link('login'));
		}
		$message = $this->request->getSession()->get('ProtectedAreaUserMessage');
		$this->request->getSession()->set('ProtectedAreaUserMessage',false);
		return $this->Customise(array(
			'ProtectedAreaUserMessage' => $message
		));
	}
	
	public function change_password()
	{
		return $this->renderWith(array('ProtectedAreaPage_change_password','Page'));
	}
	
	public function reset_password()
	{
		if ($this->ProtectedAreaUser())
		{
			$this->ProtectedAreaUser()->Logout();
		}
		return $this->renderWith(array('ProtectedAreaPage_reset_password','Page'));
	}
	
	public function logout()
	{
		if ($User = $this->ProtectedAreaUser())
		{
			$User->Logout();
		}
		return $this->redirect($this->Link());
	}
	
	public function login()
	{
		if ($this->ProtectedAreaUser())
		{
			$this->ProtectedAreaUser()->Logout();
		}
		return $this->renderWith(array('ProtectedAreaPage_login','Page'));
	}
	
	public function ProtectedAreaUserLoginForm()
	{
		$fields = Forms\FieldList::create();
		$fields->push( Forms\TextField::create('Email','Email') );
		$fields->push( Forms\PasswordField::create('Password','Password') );
		$fields->push( Forms\HiddenField::create('BackURL','') );
		if ( ($BackURL = $this->request->getVar('BackURL')) || ($BackURL = $this->request->getSession()->get('BackURL')) )
		{
			$this->request->getSession()->set('BackURL',false);
			$fields->dataFieldByName('BackURL')->setValue($BackURL);
		}
		$actions = Forms\FieldList::create(
			Forms\FormAction::create('doProtectedAreaUserLogin','Login')
		);
		
		$validator = FormUtilities::RequiredFields(
			$fields,
			array(
				'Email',
				'Password'
			)
		);
		
		$form = Forms\Form::create(
			$this,
			'ProtectedAreaUserLoginForm',
			$fields,
			$actions,
			$validator
		);
		
		return $form;
	}
	
	public function doProtectedAreaUserLogin($data,$form)
	{
		if ( (!$Email = $data['Email']) || (!$Password = $data['Password']) )
		{
			$form->sessionMessage("You must provide an email and password to login",'bad');
			$this->request->getSession()->set('BackURL',$data['BackURL']);
			return $this->redirect($this->Link());
		}
		if (!$User = ProtectedAreaUser::get()->filter(array('Email' => $Email,'Active' => 1))->First())
		{
			$form->sessionMessage("Invalid email or password",'bad');
			$this->request->getSession()->set('BackURL',$data['BackURL']);
			return $this->redirect($this->Link());
		}
		if ($User->CheckPassword($data['Password']))
		{
			$User->Login();
			$User->ClearTemporaryPassword();
			$this->request->getSession()->set('BackURL',false);
			return $this->redirect( ($data['BackURL']) ? urldecode($data['BackURL']) : $this->Link() );
		}
		if ($User->CheckTempPassword($data['Password']))
		{
			$User->Login();
			$User->ConvertTemporaryPassword();
			$this->request->getSession()->set('BackURL',false);
			return $this->redirect( ($data['BackURL']) ? urldecode($data['BackURL']) : $this->Link() );
		}

		$form->sessionMessage("Invalid email or password",'bad');
		$this->request->getSession()->set('BackURL',$data['BackURL']);
		return $this->redirect($this->Link());
	}
	
	public function ChangeProtectedAreaUserPasswordForm()
	{
		$fields = Forms\FieldList::create();
		$fields->push( Forms\ReadonlyField::create('Email','Email')->setValue($this->ProtectedAreaUser()->Email) );
		$fields->push( Forms\PasswordField::create('OldPassword') );
		$fields->push( Forms\PasswordField::create('NewPassword') );
		$fields->push( Forms\PasswordField::create('ConfirmPassword') );
		
		return Forms\Form::create(
			$this,
			'ChangeProtectedAreaUserPasswordForm',
			$fields,
			Forms\FieldList::create(
				Forms\FormAction::create('doChangeProtectedAreaUserPassword','Save')
			),
			FormUtilities::RequiredFields(
				$fields,
				array(
					'OldPassword',
					'NewPassword',
					'ConfirmPassword'
				)
			)
		);
	}
	
	public function doChangeProtectedAreaUserPassword($data,$form)
	{
		$User = $this->ProtectedAreaUser();
		if (!$data['OldPassword'] || !$data['NewPassword'] || !$data['ConfirmPassword'])
		{
			$form->sessionMessage('Please complete all fields','bad');
			return $this->redirectBack();
		}
		if ($data['NewPassword'] != $data['ConfirmPassword'])
		{
			$form->sessionMessage('Password and confirm password do not match','bad');
			return $this->redirectBack();
		}
		if ( (!$User->CheckPassword($data['OldPassword'])) && (!$User->CheckTempPassword($data['OldPassword'])) )
		{
			$form->sessionMessage('Invalid Password','bad');
			return $this->redirectBack();
		}
		if (strlen($data['NewPassword']) < Config::inst()->get(ProtectedAreaUser::class,'min_password_length'))
		{
			$form->sessionMessage('Password must be at least '.Config::inst()->get(ProtectedAreaUser::class,'min_password_length').' characters','bad');
			return $this->redirectBack();
		}
		$User->ChangePassword = $data['NewPassword'];
		$User->TempPassword = null;
		$User->TempPasswordSalt = null;
		$User->write();
		$this->request->getSession()->set('ProtectedAreaUserMessage','Your Password Has Been Saved!');
		return $this->redirect($this->Link());
	}
	
	public function ResetProtectedAreaUserPasswordForm()
	{
		$fields = Forms\FieldList::create();
		$fields->push( Forms\EmailField::create('Email','Email') );
		
		$actions = Forms\FieldList::create(
			Forms\FormAction::create('doResetProtectedAreaUserPassword','Submit')
		);
		
		$validator = FormUtilities::RequiredFields(
			$fields,
			array(
				'Email'
			)
		);
		
		$form = Forms\Form::create(
			$this,
			'ResetProtectedAreaUserPasswordForm',
			$fields,
			$actions,
			$validator
		);
		
		return $form;
	}
	
	public function doResetProtectedAreaUserPassword($data,$form)
	{
		if (!$Email = $data['Email'])
		{
			$form->sessionMessage('Please provide an email address','bad');
			return $this->redirectBack();
		}
		if ($User = ProtectedAreaUser::get()->find('Email',$Email))
		{
			$User->ResetPassword();
		}
		return $this->Customise(array(
			'Content' => '<p>Check your inbox for an email containing a temporary password. 
				If you do not recevie an email, then there is no account with that email address.</p>
				<p><a href="'.$this->Link('login').'">Click here to login</a></p>'
		))->renderWith('Page');
	}
}









