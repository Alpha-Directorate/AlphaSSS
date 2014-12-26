# Flow of Events for *Request Invitation* Use Case 12

## Brief Description

The use case is typically started by a non-member (it may also be started by logged-out member.) It allows him/her to request an invitation code to register as a new member. See also *[Use Case 2 - Register](UC2-Register)*.

## Preconditions

* <a name="12.11"></a>`12.11` This use case can be started by any user who is *not* currently logged-in. The primary purpose of the use case is for the non-member to request invitation code. However, since we do *not* have a rule "one account per one person", any existing member can log out and start *[UC2 - Register]()* use case to create a new, separate membership account (provided of course that he/she has a valid invitation code). See also: *[UC4 - Log In/Out]()* (TODO link).

## Main Flow

* <a name="12.1"></a>`12.1` The use case begins when a non-member browses the members listing and clicks on the button "Request invitation" *[(S-1)](#s-1-several-request-invitation-buttons-clicked-multiple-times)*, see the mockup below. See also *(UC3 - Browse Listing)* (TODO).

* <a name="12.13"></a>`12.13` While a non-member is browsing the members' listings or profiles, the button "Request Invitation" should appear somewhere next to the nickname/ avatar of the members who are currently online, see the mockup below. See also *[System Requirement 12.8](#12.8)* below.

![Mockup for UC12-Request Invitation: Browse Member Listing to Request Invitation](images/UC12-Request-Invitation-Step-1.png)<br>
**An example mockup of non-member requesting invitation, first step.**

* <a name="12.14"></a>`12.14` The button "Request Invitation" must never appear anywhere on the site while a logged-in member is browsing the site.

* <a name="12.2"></a>`12.2` After the non-member clicks the "Request Invitation" button, the system shall attempt in real-time to deliver the message to the the requested member. The website confirms in real-time, on the same page, that the invitation request has been delivered. See also the *[System Requirement 12.10](#12.10)* and *[E-1](#e-1-member-to-whom-request-has-been-sent-is-now-offline)* below. 

![Mockup for UC12-Request Invitation: Confirm That Invitation Has Been Sent](images/UC12-Request-Invitation-Confirmation.png)<br>
**An example mockup of the confirmation that request has been sent.**

* <a name="12.3"></a>`12.3` After the member clicks on the button to "Deliver the Code", see *[UC1 - Invite Someone, requirement 1.18](UC1-Invite-Someone#1.18)*, the system displays to the non-member the newly generated invitation code with a link/button to complete the registration, see an example mockup below. The use case ends.

![Mockup for UC12-Request Invitation: Code Has Been Delivered](images/UC12-Request-Invitation-Code-Delivered.png)<br>
**An example mockup of the newly generated invitation code.**

## Subflows

### S-1. Several "Request Invitation" buttons clicked multiple times.

* <a name="12.4"></a>`12.4` The non-member can click the "Request Invitation" buttons multiple times for several members who are currently online. See also requirement [`12.1`](https://github.com/alpha-social-club/alpha-social-development/wiki/UC12-Request-Invitation#12.1) above.

* <a name="12.5"></a>`12.5` The system shall deliver, in real-time, the invitation code requests to all members whose "Request Invitation" buttons have been clicked. See also *[UC1 - Invite Someone, requirement 1.17](UC1-Invite-Someone#1.17)*.

* <a name="12.6"></a>`12.6` In this scenario, only *one* invitation code should be displayed to the non-member. This should be the code that comes from the member who is the first, i.e. *the quickest* to respond and to click on the button "Deliver the Code". See also the [requirement 12.3](#12.3) above. The use case ends.

### S-2. Request invitation code by posting in the general forum.

TODO (Need UC8 - Participate in Forum).

## Alternative Flows

### E-1. Member, to whom request has been sent, is now offline.

* <a name="12.7"></a>`12.7` The system may not be always able to respond rapidly, in real-time and with perfect synchronicity, to the member change of presence (online vs. offline). If the non-member sends a request for invitation code to a member who has been online just a moment ago, but the actual presence has changed in the meantime to offline, then the invitation code shall not be delivered to the member. See also the [requirement 12.8](#12.8) below.

* <a name="12.9"></a>`12.9` In the scenario described in `12.7` above, the system will display an error message on the same page so that the non-member can take further action, see also the requirement '12.10' and an example mockup below). The use case continues.

![Mockup for UC12-Request Invitation: The Member is Now Offline](images/UC12-Request-Invitation-Error-Offline-Member.png)<br>
**An example mockup of the error message when the member went offline.**

## System Requirements

* <a name="12.10"></a>`12.10` All the user feedback display messages, such as alerts or confirmations, should be displayed in-place without redirecting to a new page, using for example AJAX or jQuery popups or overlays.

* <a name="12.15"></a>`12.15` The alerts or confirmation messages, specified in requirements 12.10 above, can be closed by the user by clicking on the [X] button in the top-right corner (as shown in example mockup sketches above).

* <a name="12.8"></a>`12.8` Our system should be designed and capable of detecting the member's presence online. One possibility is to interface via custom API to [iFlyChat](www.iflychat.com). This issue needs to be further discussed.

