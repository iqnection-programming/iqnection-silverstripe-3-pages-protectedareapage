<?php

use SilverStripe\Admin\ModelAdmin;

class ProtectedAreaUserModelAdmin extends ModelAdmin
{
	private static $managed_models = array(
		ProtectedAreaUser::class,
		ProtectedAreaUserGroup::class
	);
	
	private static $url_segment = 'secure-users';
	
	private static $menu_title = 'Secure Users';
	
	private static $menu_icon = 'iq-protectedareapage/images/user-management.png';
	
}