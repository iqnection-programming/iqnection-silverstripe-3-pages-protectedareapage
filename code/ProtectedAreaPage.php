<?php


class ProtectedAreaPage extends Page
{
	private static $icon = 'iq-protectedareapage/images/secure-area-page.png';
	
	private static $defaults = array(
		'ShowInMenus' => false,
		'ShowInSearch' => false
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Help', LiteralField::create('help',file_get_contents(PROTECTED_AREA_PAGE_ROOT.'/instructions.html')) );
		if (!ProtectedAreaUserGroup::get()->Count())
		{
			$fields->addFieldToTab('Root.Users', HeaderField::create('Users','You must create User Groups before creating users',2) );
		}
		else
		{
			$fields->addFieldToTab('Root.Users', GridField::create(
				'ProtectedAreaUsers',
				'Users',
				ProtectedAreaUser::get(),
				GridFieldConfig_RelationEditor::create()
			));
		}
		$fields->addFieldToTab('Root.User Groups', GridField::create(
			'ProtectedAreaUserGroups',
			'User Groups',
			ProtectedAreaUserGroup::get(),
			GridFieldConfig_RelationEditor::create()
		));
		return $fields;
	}
	
	public function ProtectedPages()
	{
		$ProtectedPages = new ArrayList();
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

class ProtectedAreaPage_Controller extends Page_Controller
{
	private static $allowed_actions = array(
		'login',
		'ProtectedAreaUserLoginForm',
		'reset_password',
		'ResetProtectedAreaUserPasswordForm',
		'change_password',
		'ChangeProtectedAreaUserPasswordForm',
		'logout',
	);
	
	public function PageCSS()
	{
		return array_merge(
			parent::PageCSS(),
			array(
				$this->themeDir().'/css/form.css'
			)
		);
	}
	
	public function ProtectedAreaUserConfig($var)
	{
		return Config::inst()->get('ProtectedAreaUser',$var);
	}
		
	public function index()
	{
		if (!$this->ProtectedAreaUser())
		{
			return $this->redirect($this->Link('login'));
		}
		$message = Session::get('ProtectedAreaUserMessage');
		Session::set('ProtectedAreaUserMessage',false);
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
		return $this->renderWith(array('ProtectedAreaPage_reset_password'));
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
		return $this->renderWith(array('ProtectedAreaPage_login'));
	}
	
	public function ProtectedAreaUserLoginForm()
	{
		$fields = FieldList::create();
		$fields->push( TextField::create('Email','Email') );
		$fields->push( PasswordField::create('Password','Password') );
		$fields->push( HiddenField::create('BackURL','') );
		if ( ($BackURL = $this->request->getVar('BackURL')) || ($BackURL = Session::get('BackURL')) )
		{
			Session::set('BackURL',false);
			$fields->dataFieldByName('BackURL')->setValue($BackURL);
		}
		$actions = FieldList::create(
			FormAction::create('doProtectedAreaUserLogin','Login')
		);
		
		$validator = FormUtilities::RequiredFields(
			$fields,
			array(
				'Email',
				'Password'
			)
		);
		
		$form = Form::create(
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
			Session::set('BackURL',$data['BackURL']);
			return $this->redirect($this->Link());
		}
		if (!$User = ProtectedAreaUser::get()->filter(array('Email' => $Email,'Active' => 1))->First())
		{
			$form->sessionMessage("Invalid email or password",'bad');
			Session::set('BackURL',$data['BackURL']);
			return $this->redirect($this->Link());
		}
		if ($User->CheckPassword($data['Password']))
		{
			$User->Login();
			$User->ClearTemporaryPassword();
			Session::set('BackURL',false);
			return $this->redirect( ($data['BackURL']) ? urldecode($data['BackURL']) : $this->Link() );
		}
		if ($User->CheckTempPassword($data['Password']))
		{
			$User->Login();
			$User->ConvertTemporaryPassword();
			Session::set('BackURL',false);
			return $this->redirect( ($data['BackURL']) ? urldecode($data['BackURL']) : $this->Link() );
		}
		$form->sessionMessage("Invalid email or password",'bad');
		Session::set('BackURL',$data['BackURL']);
		return $this->redirect($this->Link());
	}
	
	public function ChangeProtectedAreaUserPasswordForm()
	{
		$fields = FieldList::create();
		$fields->push( ReadonlyField::create('Email','Email')->setValue($this->ProtectedAreaUser()->Email) );
		$fields->push( PasswordField::create('OldPassword') );
		$fields->push( PasswordField::create('NewPassword') );
		$fields->push( PasswordField::create('ConfirmPassword') );
		
		return Form::create(
			$this,
			'ChangeProtectedAreaUserPasswordForm',
			$fields,
			FieldList::create(
				FormAction::create('doChangeProtectedAreaUserPassword','Save')
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
		if (strlen($data['NewPassword']) < Config::inst()->get('ProtectedAreaUser','min_password_length'))
		{
			$form->sessionMessage('Password must be at least '.Config::inst()->get('ProtectedAreaUser','min_password_length').' characters','bad');
			return $this->redirectBack();
		}
		$User->ChangePassword = $data['NewPassword'];
		$User->TempPassword = null;
		$User->TempPasswordSalt = null;
		$User->write();
		Session::set('ProtectedAreaUserMessage','Your Password Has Been Saved!');
		return $this->redirect($this->Link());
	}
	
	public function ResetProtectedAreaUserPasswordForm()
	{
		$fields = FieldList::create();
		$fields->push( EmailField::create('Email','Email') );
		
		$actions = FieldList::create(
			FormAction::create('doResetProtectedAreaUserPassword','Submit')
		);
		
		$validator = FormUtilities::RequiredFields(
			$fields,
			array(
				'Email'
			)
		);
		
		$form = Form::create(
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









