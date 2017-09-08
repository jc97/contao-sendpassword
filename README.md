# Generate and send passwords to users in Contao 

Developed by Julian Knorr.

## Synopsis

This is an extension for the Contao Open Source CMS.
It's main feature is automatically generate random passwords for users and members and sending this password to them by email.
Additionally this extension allows generating usernames for members from first name and last name automatically.

## Dependencies and languages

This extension was developed and tested for Contao 3.5.x.
Probably it is possible to install it under Contao 4 too.

Furthermore this extension depends on the newsletter extension for Contao, because it uses it's icons.

This extension supports english and german language in back end and for the emails.   

## Installation

To install this extension copy it's file to `system/modules/sendpassword/`. 
After that just clear the cache of Contao.

## Configuration and use

To use this extension the name and email address of the site's administrator have to be defined in the configuration of Contao.  

Only admins are allowed to send new passwords to multiple users or members.
All users that have access to the password field of the corresponding table are allowed to send a new password to a single user or member.

## Troubleshooting

Directly here on GitHub.

## License

This extension is licensed under the terms of the LGPLv3. See [LICENSE](LICENSE). 