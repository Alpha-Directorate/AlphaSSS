# Flow of Events for *Private Group Chat* Use Case 9

## Preconditions

* <a name="9.1"></a>`9.1` A member must belong to the pilot's private group—further referred to as the pilot's *circle*—for this use case to begin *[(S-3)](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#s-3-member-who-is-not-in-the-pilots-circle-attempts-to-enter-hisher-lounge)*.

## Main Flow

* <a name="9.2"></a>`9.2` This use begins when the pilot's presence online is detected *[(S-2)](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#s-2-attempt-to-enter-lounge-when-the-pilot-is-offline)*. When this happens, the lounge (the chat room) is created and the user interface elements become available to other members.

* <a name="9.3"></a>`9.3` A member enters the pilot's lounge by clicking/tapping on a link or a button *[(S-3)](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#s-3-member-who-is-not-in-the-pilots-circle-attempts-to-enter-hisher-lounge)*.

* <a name="9.4"></a>`9.4` The members and the pilot may participate in real-time exchange of text messages with all members who are currently inside the lounge chat. Note the * lounge room* is the word used for the *chat room*.

* <a name="9.5"></a>`9.5` The pilot acts in the role of the administrator of the lounge, but only within his/her own circle. In other words, if the pilot enters some other pilot's lounge *[(S-3)](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#s-3-member-who-is-not-in-the-pilots-circle-attempts-to-enter-hisher-lounge)*, he/she can do so only as a regular member.

* <a name="9.6"></a>`9.6` Naturally, the site administrator has the highest role (capabilities) to administer and moderate all circles and all lounges.

* <a name="9.7"></a>`9.7` When the pilot goes offline, the lounge terminates *[(S-1)](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#s-1-the-pilot-goes-offline)*. The use case ends.

## Subflows

### *(S-1)* The pilot goes offline.

* <a name="9.8"></a>`9.8` When the pilot goes offline, and if there are any members present inside the lounge room, a message (e.g. via AJAX popup) is immediately displayed to the members that the pilot has left the room and that the lounge room is now closed.

* <a name="9.9"></a>`9.9` The lounge room window becomes non-responsive to any new user inputs, and the member can now click a button to close the window or leave this page. The use case ends.

### *(S-2)* Attempt to enter lounge when the pilot is offline.

* <a name="9.10"></a>`9.10` Using some UI indication (e.g. grayed-out, inactive button state), the system must prevent any member(s) from entering the lounge if the pilot-owner of this lounge is offline. (Essentially, the door to this lounge is locked, therefore it must not be entered.)

### *(S-3)* Member, who is not in the pilot's circle, attempts to enter his/her lounge.

* <a name="9.16"></a>`9.16` A member cannot enter the pilot's lounge room unless he has been previously accepted into the pilot's circle (TODO, reference to a place where the "pilot's circle acceptance" is explained).

* <a name="9.17"></a>`9.17` In this case, the member is either redirected to a different page or presented with an AJAX popup (be user-friendly, minimize friction, don't disrupt the flow) informing him/her that first the pilot needs to accept him/her into the circle (TODO, reference to a place where the "pilot's circle acceptance" is explained).

## Alternative Flows
None.

## Additional System Requirements and Constraints

* <a name="9.13"></a>`9.13` The ASC (Alpha Social Club) system shall use iFlyChat plugin. Even though the requirements below are generic, this section addresses the default behavior of iFlyChat in particular to ensure that the ASC-specific needs are satisfied.

* <a name="9.11"></a>`9.11` Within the entire ASC site, all the lounge rooms must be *non-overlapping*. It means that any one member, including any pilot, can exist in one and only one room at any point in time.

* <a name="9.12"></a>`9.12` A pilot must be able to moderate his own lounge room. But he/she must *not* be permitted to moderate any other lounge rooms. The system must ensure any such actions are impossible. Also, see [`9.5`](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#9.5).

* <a name="9.14"></a>`9.14` All members and the pilot inside a lounge room can exchange text messages only within its circle and nowhere else. Nobody -- not even the administrator -- is permitted to open a separate 1-to-1 or any other additional lounge room window(s) embedded within the pilot's circle (group). <sup>1</sup>

* <a name="9.15"></a>`9.15` Site admin can enter any live lounge room. Note that the requirement [`9.11`](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#9.11) above still holds.

------

1) The requirement [`9.14`](https://github.com/alpha-social-club/alpha-social-development/wiki/UC9-Private-Group-Chat#9.14) may be changed for some future ASC release. For now, I need to place the above restriction for the first release of the system. But as the site evolves, we might require more flexibility.
