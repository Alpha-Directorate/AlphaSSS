=== Private groups ===
Contributors: Robin Wilson
Tags: forum, bbpress, bbp, private, groups
Requires at least: 3.0.1
Donate link: http://www.rewweb.co.uk/donate
Tested up to: 4.1.1
Stable tag: 2.5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

For bbPress - Creates private forum groups

== Description ==
This Plugin creates unlimited private forum groups.  

Forums are then allocated to one or more groups, and users allocated to one or more groups.

What this achieves
unlimited private groups
Each user is set to a group or groups, and each forum can have any or all the groups associated with it
Any number of public forums combined with any number of group forums.  The group forums can be individually set to public or private.
Forum title and description (but not topics or replies) can be set to be visible to non-group users, allowing people to see that a forum exists but not access it
Separate pages can be set to allow redirection of non group users for sign-up or information

Example

So if
User a belongs to group 1
User b belongs to group 2
User c belongs to group 3

and
Forum x is set to allow group 2
Forum y is set to allow group 2 and group 3
Forum z is set to allow group 1 and group 3

Then
User a can access only forum z
User b can access forum x and forum y
User c can access forum y and forum z

Restrictions/warnings

The widgets (bbpress) forums list, (bbpress) recent replies, and (bbpress) recent topics SHOULD NOT BE USED, as they will show topics headings and author names for all forums.  Replacement widgets called (private groups) forums list, (private groups) recent replies, and (private groups) recent topics are available instead



Works with bbpress 2.5.3 or higher



== Installation ==
To install this plugin :

1. Go to Dashboard>plugins>add new
2. Search for 'private groups'
3. Click install
4. and then activate
6. go into settings and set up as required.

<strong>Settings</strong>

Go to Dashboard>settings>Private Groups

There are 4 settings tabs and 2 management tags.

<em><strong>Forum Visibility tab.</strong></em>

This tab allows you to set forum visibility.

By default where the forum has groups set, then these are only visible to authorised users.  However you may want users to see that forums exist (to attract new forum users), but not to see content.

For instance on a cookery site you might have a cake group, who exchange recipes and advice on cakes.  You might want people to sign up before being able to contribute, but if they don't know the forum exists, they won't join.

So by listing the forums (and optionally the description - see tab below) users can see they exist, and if they click the forum or freshness links, they can be taken to any url or wordpress page you wish.  Typically this might be a sign-up page, or a 'you can't access' page. For instance you page might say

Sorry, you need to be a member to see this area. To join click here Login if need-be [bbp-login]

In this tab, you can set whether non-group members can see the forums in the indexes (but not access).  If the forum is set to public, then both non-logged in and logged in users will see this.  If the forum is set to private, then only logged in users will see the existence of these forums. this gives a highly granular approach to what forums are displayed for different groups.

If visibility is set, then  there are options for redirecting, and what freshness messages are displayed.

<em><strong>General Settings</strong></em>

In general settings, you have the ability to hide topic and reply counts, show sub-forum descriptions, and remove the 'private' prefix from the forum displays.

<em><strong>Group Name Settings</strong></em>

Here you set 'friendly' names for the groups, to help you remember.  These names do not affect how the restrictions work, group 1 will remain group 1 whatever you name it.

<strong>To set forums</strong>

For each restricted forum
<ol>
	<li>Go in to Dashboard&gt;forums and select the forum you wish to restrict.</li>
	<li>Under the text you’ll see a box called ‘Forum Groups’ – select the group or groups you wish to allow to access this forum</li>
	<li>If you wish to have a custom error message, you can set one here.</li>
</ol>
<strong> Setting Widgets</strong>

The bbPress topics and replies widgets will still at this stage show <b>all</b> topic and reply titles etc. If a topic/reply is selected this will give an error message, but titles and authors will be visible, which might be embarrassing !

So you will probably not want people to see these subjects, so there are 3 new widgets that the plugin has added that filter this to only show appropriate content.

Go in to Dashboard&gt;appearance&gt;widgets
<ol>
	<li>You will see three new widgets starting with (private groups) and covering topics, replies and Topics lists.</li>
	<li>If you are using the standard bbPress topic, reply or forum list widgets, you should remove these from your sidebar and replace them with the (private groups) ones</li>
</ol>
<strong> </strong>

<em><strong>Management Information</strong></em>
This tab lists the groups, what forums are allocated to them, and the number of users each group has

<em><strong>User Management</strong></em>
This tab allows for the bulk change of users between (and to and from) the groups, and via user edit allows multiple group allocation

<em><strong>Assign groups to roles</strong></em>
This tab allow those of you who use membership plugins etc. to assign a group against a wordpress or custom role. 


To set forums

For each restricted forum
1.Go in to Dashboard>forums and select the forum you wish to restrict.
2.Under the text you’ll see a box called ‘Forum Groups’ – select the group or groups you wish to allow to access this forum


Setting Widgets

The bbPress topics and replies widgets will still at this stage show all topic and reply titles etc. If a topic/reply is selected this will give an error message (see below).

However you will probably not want people to see these subjects, so there are two widgets that the plugins have added that filter this to only show appropriate content.

Go in to Dashboard>appearance>widgets
1.You will see three new wiggets starting with '(Private Groups)' and covering topics, replies and forum list.
2.If you are using the standard bbPress topic, reply or forum list widgets, you should remove these from your sidebar and replace them with the ‘tehnik’ ones

Shortcode

A shortcode to list users either across all groups or per group

[list-pg-users ]  lists all groups and their users

[list-pg-users group=$group] lists users of a single group name eg [list-pg-users group='developers']


== Screenshots ==
1. A sample forum setup screen
2. Setting a user to a group




== Changelog ==

2.5.6 change to forum_filters.php and topics.php to correct minor syntax errors https://wordpress.org/support/topic/couple-of-warnings-in-latest-version?replies=6 and https://wordpress.org/support/topic/bbp_list_forums-filter-bug?replies=2

2.5.5 add latest activity widget, corrected sub-forum display and added column option

2.5.4 remove author link filter as not required

2.5.3 fix search pagnination

2.5.2 correction to moderator role to prevent moderators seeing other private forums

2.5.1 minor error fix for 'remove private prefix' casuing illegal offset

2.5 updated to allow internationalization

2.4 Correction to management information user count

2.3 Minor correction

2.2 Multiple groups for users added

2.1  Optional assign users to roles added to settings

2.0.1 list user shortcode added
User Management (bulk change of users) added
Management information - list of forums added


1.9.2 Fix for array error on forum visibility
improved filter for topic subscriptions

1.9.1  Fix for moderator role in group views

1.9 fix to forum subscription display in profile

1.8.1  bug fix for search function returning incorrect data when allowed posts was nil

1.8 bug fix where forum is top level forum
topic/reply/forum types implemented throughout

1.7 Moderators can now be set to moderate all forums or just their group ones (plus any open forums)
Improved topic filtering to speed performance
allow Super sticky topics to be seen by all forums

1.6 Shortcode [bbp-topic-form] now only shows allowed forums

1.5 Amended to have unlimited groups

1.4.1 compatibility with 'mark as read' plugin

1.3 topics and replies paging fixed

1.2 Author and replies issues fixed

1.1 Minor changes

1.0 Version 1
