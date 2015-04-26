=== DM Confirm Email ===
Contributors: donmhico
Tags: spam, security, registration, email, confirm, protect, users, register
Requires at least: 3.6
Tested up to: 3.8.1
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect your wordpress site with spam registration. DM Confirm Email requires new users to confirm their email addresses.

== Description ==

Having so many spam registrations? Tired of getting fake users with fake emails? Good news! DM Confirm Email will solve your problems.
DM Confirm Email will send a confirmation email and the only time it will actually "create" the account for the user if the email address is confirmed.

Also allows you to send a welcome message to newly confirmed and created users which is great to give your new users initial instructions or other information that can be helpful to new users.

[DM Confirm Email](http://donmhi.co/projects/dm-confirm-email) integrates seamlessly with wordpress registration system and uses all native registration hooks which allows all your current customization and plugins to the registration work.

= Additional Resources =
* [See DM Confirm Email in action](http://donmhi.co/projects/dm-confirm-email/#demo)
* [Review the plugin and let me know what you think!](http://wordpress.org/support/view/plugin-reviews/dm-confirm-email)
* [Have a question? Or found a bug?](http://wordpress.org/support/plugin/dm-confirm-email)
* [For suggestions and ideas for future release. Comment here](http://donmhi.co/projects/dm-confirm-email)
* [Follow me @donMhico](https://twitter.com/donmhico)

= Features =
* Reduce unwanted and spam registration.
* Verifies and confirms email addresses of user registrations.
* Customize the confirmation email that will be sent.
* Allows html email content.
* Resend confirmation email feature
* Define the number of days before the confirmation keys will be expired.
* Customize all warning and successful messages in the wordpress side.
* Ability to send welcome message to new users.
* Prevents waste of resources and web space by only creating user account to confirmed emails.
* Uses all the native registration hooks for more advanced customization.
* Seamless integration
* NEW! Ability to edit the email message containing the password of the new account that will be sent to the user.

= Future =
* Display all pending registrations that need confirmation on the Dashboard.

== Installation ==

[Install like all other plugins](http://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation).

== Frequently Asked Questions ==
[How the DM Confirm Email works?].(http://donmhi.co/projects/dm-confirm-email/#howitworks)

== Screenshots ==
1. Upon registration
2. Confirmation email sent to the user
3. Successful confirmed email
4. DM Confirm Email settings

== Changelog ==

= 1.4 =
* Added the ability to edit and customize the email content containing the new account information (username and password).
* New and better organized Settings page UI.

= 1.3 =
* Added the ability to send welcome message to newly registered users.

= 1.2 =
* Fix major issue with hostings running on PHP 5.2 and below.

= 1.1 =
* Added a "Resend confirmation link" feature
* Integrated the "Resend confirmation link" in the registration page.

== Upgrade Notice ==

= 1.3 =
Add the ability to send a welcome message.

= 1.2 =
Fix major issue, "Parse error: syntax error, unexpected T_STRING in /wp-content/plugins/dm-confirm-email/dm-confirm-email.php on line 12" when activating the plugin