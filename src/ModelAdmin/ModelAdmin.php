<?php

namespace IQnection\ProtectedArea\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use IQnection\ProtectedArea\Model\ProtectedAreaUser;
use IQnection\ProtectedArea\Model\ProtectedAreaUserGroup;

class ProtectedAreaModelAdmin extends ModelAdmin
{
	private static $managed_models = array(
		ProtectedAreaUser::class,
		ProtectedAreaUserGroup::class
	);
	
	private static $url_segment = 'secure-users';
	
	private static $menu_title = 'Secure Users';
	
	private static $menu_icon = 'iqnection-pages/protectedareapage:images/user-management.png';
	
}