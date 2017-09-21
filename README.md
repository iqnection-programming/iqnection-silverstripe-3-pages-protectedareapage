# iqnection-silverstripe-3-pages-protectedareapage
SilverStripe 3 Protected Area Page

## Features
protects all pages that are children of a ProtectedAreaPage

Uses independant user management system from SilverStripe Member

Users must login before they can access the content

Users are assigned to groups, groups have access to the pages specified

## Config:
ProtectedPagesUser:
  admin_can_set_password : true


### If true, admins can set the password for users
$admin_can_set_password bool [default: false]

### Allow users to change their password
$user_can_update_password bool [default: true]

### Auto generate password for new users
$auto_generate_password bool [default: true]

### Minimum required password length
$min_password_length int [default: 1]

### Just a random unique string that is used in teh password salt
$secure_salt string

### Cookie name used for user session
$cookie_name string [default: '_ppu']

### Cookie lifetime
$cookie_lifetime int [default: 1]