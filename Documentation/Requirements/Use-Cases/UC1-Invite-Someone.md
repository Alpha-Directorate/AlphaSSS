# Flow of Events for *Invite Someone* Use Case 1

## Brief Description

The only way for a non-member to join the Alpha Social Club is by invitation. This use case provides a mechanism for a member to invite any non-member to join the club and become a member.

## Preconditions

* <a name="1.1"></a>`1.1` Only a logged-in member can invite a non-member to join the club. Therefore, for this use case to begin, the main flow of the *[Use Case 2 - Register](UC2-Register#main-flow)* must have completed successfully.

## Main Flow

* <a name="1.12"></a>`1.12` This use case begins when a currently logged-in member uses the site and proceeds to issue an invitation to a non-member. There are 2 ways for the member to invite the non-member to join the club:

  1. Send the invitation code automatically, in "real-time", in response to a request from the non-member who is using the system at the same time as this member is. The scenario is described here in this Main Flow section of this use case.

  1. Generate an invitation code manually and deliver it through some external method of communication (e.g. SMS, email, telephone call, Facebook, Twitter etc.) to a non-member. The scenario is described in the *[Subflow S-1](#s-1-send-invitation-code-via-an-external-message)* below.

* <a name="1.17"></a>`1.17` If the invitation request is triggered in "real-time" by a non-member who presses the "Request Invitation" button (see also *[UC12 - Request Invitation](UC12-Request-Invitation#main-flow)*), then the system shall generate the invitation code automatically, without any additional action(s) required of the member. See the example mockup below. See also *[System Requirements - Invitation Code Generation Rules](#invitation-code-generation-rules)* below.

![Mockup for UC1-Invite Someone: Real-Time Invitation Code Request](images/UC1-Invite-Real-Time.png)<br>
**An example mockup of the real-time non-member request with automatic code generation.**

* <a name="1.18"></a>`1.18` The invitation message shall appear persistently and cannot be dismissed in any other way except by clicking on the "Deliver the Code" button. If the member does not click the button, and navigates to another page within our domain, then the invitation message must immediately pop up on any other page for as long as the member is on our site. Note: The intent of this requirement is to emphasize the importance of these requests, and to make sure that all such requests from  non-members to join the club are handled promptly. (Of course, we can do nothing about the user navigating away to another website or closing the browser window.)

* <a name="1.21"></a>`1.21` The invitation popup message shall disappear automatically if the new member has already registered, and therefore the invitation code is no longer needed. Note such situation might occur if the non-member receives multiple invitation codes, see *[UC12, S-1. Several "Request Invitation" buttons clicked multiple times](UC12-Request-Invitation#s-1-several-request-invitation-buttons-clicked-multiple-times)*.

* <a name="1.20"></a>`1.20` After the "Deliver the Code" button has been pressed, the message shall disappear, and the system shall not generate any more confirmation message or pop-ups of any sort regarding this specific invitation and/or registration of this new member. (We really do not wish to piss off our existing members by interrupting their flow with annoying popups any more than necessary. But! in order to register the new member without delay we need to be just a little bit "pushy".) The use case ends.

## Subflows

### S-1. Send invitation code via an external message.

* <a name="1.2"></a>`1.2` This subflow begins when a member navigates to his "Invitations" section on the left-side navigation menu. See the example mockup below.

* <a name="1.22"></a>`1.22` The member clicks on the button to generate a new invitation code. See the mockup below.

![Mockup for UC1-Invite Someone: Manual Code Generation Step 1](images/UC1-Request-Invitation-Generate-Step-1.png)<br>
**An example mockup of manual invitation code generation, step 1**

* <a name="1.23"></a>`1.23` The system generates a new unique invitation code, and the user interface displays the code in-place (e.g. using AJAX technology) without redirecting the user to a different page. See the mockup below.

![Mockup for UC1-Invite Someone: Manual Code Generation Step 2](images/UC1-Request-Invitation-Generate-Step-2.png)<br>
**An example mockup of manual invitation code generation, step 2**

* <a name="1.3"></a>`1.3` The member delivers the code to the non-member by email, by phone, by text message, by raven, by bike messenger, by teleportation, or by any other means. We do not care how it happens, as it happens outside of our system. The use case ends.

## Alternative Flows

### E-1. A member cannot generate a valid code to invite himself/herself.
TODO.

## System Requirements

### Invitation Code Generation Rules

* <a name="1.4"></a>`1.4` A high-level of security is *not* required for automatic codes generation. Invitation code strings should be simple. The basic string generation rules are as follows:
 * Length: 4-characters long.
 * Random combination of characters: 0-9, A-Z
 * The UI should display any letters in the codes as capitals.
 * The code is always case insensitive, for example, 'abc1' is identical to 'ABC1'. See also *[UC2, E-1. Code User Input Validation Requirements](UC2-Register#e-1-invitation-code-validation-fails)*.
* <a name="1.5"></a>`1.5` A member can generate one code at a time for each click on a button. There are no restrictions on how many codes can a member generate in any single session. However, there will be no bulk generation functionality, whereas a set of multiple invitation codes are generated with each single click.
* <a name="1.6"></a>`1.6` Each invitation code shall be unique.
* <a name="1.7"></a>`1.7` In the future, after the large number of available unique codes have been exhausted, the length of the invitation code strings can increase to 5. (Approximately [1.7 million](http://math.stackexchange.com/questions/605450/using-the-english-alphabet-a-z-and-digits-0-9-how-many-combinations-are-possibl) 4-character-long invitation codes.)
* <a name="1.8"></a>`1.8` Any existing invitation code must be invalidated (see 1.6, above) after it has been used to successfully register a new member.
* <a name="1.9"></a>`1.9` Any existing invitation code must be invalidated (see 1.6, above) if it has not been used to register a new member within 24 hours from the time of its generation.
* <a name="1.25"></a>`1.25` The code expiration time should be calibratable since it is very possible we might wish to easily adjust it in the future. Therefore, please do not hard-code the numeric value in the code. Use a constant. Any  front-end user messages should also refer to the calibration constant and not a hard coded text.
* <a name="1.10"></a>`1.10` Any code generated by any  member must be tracked and stored in the persistent storage (database). Here's some (but probably not all) information that I imagine should be stored: the member who the code was generated for, date-time generated, date-time expired, who used the code to register. This information will be very important for the future *UC11 - Grow Reputation* (TODO link to the use case).

## Notes

The following is based on my quick research. *These are not requirements, just notes.*
The information might be useful for some additional background while brainstorming, designing and implementing this use case.

1. [Easy Invitation Codes]( https://wordpress.org/plugins/baw-invitation-codes/) plugin. It is no longer maintained by the developer. Therefore, it should not be relied upon for the purpose of our project. However, the code might be useful to help expand or brainstorm on the ideas contained within.
1. [CM Invitation codes]( https://www.cminds.com/cmdownloads/cm-invitation-codes/). I know nothing about this site or its author. It could be useful and fantastic, or it could be nothing, or it could be dangerous. Better exercise caution when downloading.
1. [Viral Invites]( http://pluginferno.com/products/viral-invites/). Looks quite interesting. This plugin is not free, but we can purchase it if there it might be useful for us. If it is useless, then the [refund policy]( http://pluginferno.com/about/) is perfectly fine.
1. [Invitation codes combinations number](http://math.stackexchange.com/questions/605450/using-the-english-alphabet-a-z-and-digits-0-9-how-many-combinations-are-possibl). The the lazy ones (like me), here's the math how to calculate the number of unique invitation codes.
1. [Example of tabular UI at 1:15]( https://www.youtube.com/watch?v=u6_zl1nj3cs). The plugin Pie Register is not much useful for us for the purpose of this use case. But they are using invitation codes, and the UI presentation looks good – for inspiration.