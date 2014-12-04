# Alpha Social Club
In addition to this README:

* <a href="http://www.alphasocial.club" target="_blank">www.alphasocial.club</a> (blog only, for now). Login: `admin / Alpha.Omega`
* Our <a href="https://github.com/alpha-social-club/alpha-social-development/wiki" target="_blank">wiki pages</a>

## README Table of Content
[What is This?](https://github.com/alpha-social-club/alpha-social-development#what-is-this)<br>
[Ten-Point Vision](https://github.com/alpha-social-club/alpha-social-development#ten-point-vision)<br>
[Use Case-Driven Development](https://github.com/alpha-social-club/alpha-social-development#use-case-driven-development)<br>
[License](https://github.com/alpha-social-club/alpha-social-development#license)
[How To Install](https://github.com/alpha-social-club/alpha-social-development#how-to-install)<br>
[How to Contribute](https://github.com/alpha-social-club/alpha-social-development#how-to-contribute)<br>
[Roadmap](https://github.com/alpha-social-club/alpha-social-development#roadmap)<br>
[Credits & Acknowledgements](https://github.com/alpha-social-club/alpha-social-development#credits--acknowledgements)

## What Is This?
Alpha Social Club is a social network of like-minded individuals exploring their sexuality in a safe and friendly environment. Sex is not porn. Sex is beautiful, rewarding and healthy. Sex is at the very core of human social interactions.

## Ten-Point Vision
1.	**Sexuality is healthy.**<br>It mediates biological, physiological processes (allostasis). It builds social relationships. It stimulates brain cognition and slows down aging processes.

2.	**Sexuality is a private activity.**<br>Scientists tell as that humans are quite unique among members of the animal kingdom, preferring to have sex in private… (Who would've thought?)

5.	**Pornographic visual media are not permitted.**<br>As the corollary to the above, we do not permit porn. Porn is a *public* manifestation of sexuality. But! *private* sexual meetings online are normative.

3.	**Security and privacy of personal information.**<br>We are paranoid about this. We collect absolutely nothing about users. For example, we obliterate IP addresses, and we do not collect email addresses. <a href="http://www.alphasocial.club/where-has-our-privacy-online-gone/" target="_blank">Read more...</a> *Login:* `admin / Alpha.Omega`

4.	**Club membership is by invitation only.**<br>Firstly, invitation-only membership creates a certain level of exclusivity. Secondly, it helps us further control and safe-guard minors' participation.

10.	**The club is all-inclusive.**<br>To exclude anybody from our club based on sexual orientation or gender identity would be shameful and absurd. Everybody (who's adult) is welcome.

6.	**Community governed.**<br>Our intent is to establish a self-governed social environment. Call it the "wisdom of the crowds", or in scientific parlance a "swarm intelligence".

7.	**Membership is free.**<br>No ads. No fees. No catch. We want to create the spirit of the truly organic and natural growth (pun intended) of like-minded, friendly community.

8.	**Revenue based on voluntary donations only.**<br>As a footnote to the above, we are a company who expands significant costs to run the infrastructure and pay developers. Donations are always welcome.

9.	**Openness and transparency.**<br>This project is completely open-sourced to the community. Except, of course we do not disclose any security-sensitive data or code (admin-accessible).

## Use Case-Driven Development

![Alpha Social Club UML use case diagram](https://raw.githubusercontent.com/alpha-social-club/alpha-social-development/master/images/ASC-Use-Case-Diagram-V1.png?token=AJAywHhBq6yRi3ZSj2aA4QhqsilXZv-Tks5UiImiwA%3D%3D "Alpha Social Club UML use case diagram")

* **Actors**
  * **Visitor**<br>A user that is not recognized by the system as an existing member.

  * **Member**<br>A user who is recognized by (e.g. via a cookie) or currently logged-in to the system.

  * **Pilot**<br>A subtype of a member who can initiate private group chat (UC9) and voice video chat (UC10) use cases. Regular member cannot do that.

  * **Admin**<br>This is equivalent to the WordPress administrator role. The all-powerful user.


* **UC1 – Invite Someone**<br>A visitor can only become a new member when invited by a current member. Presently, the invitation mechanism works by means of invitation codes.

* **UC2 – Register**<br>Implemented with Gravity Forms. Email is not collected to comply with the project vision, item 4. (Password recovery will need further elaboration.)

* **UC3 – Browse Listing**<br>There are a few important aspect to this: sorting, filtering and criteria. This use case calls UC11 "Points Accumulation" to sort the results output.

* **UC4 – Log In/Out**<br>Basic functionality provided by WordPress. In the future, it would be nice to customize the appearance, look-and-feel of the login form.

* **UC5 – Edit Profile**<br>The functionality is built atop the BuddyPress and Gravity Forms. In the future, the pilots will need additional set of form(s) for their data submission.

* **UC6 – Search**<br>This is only applicable to searching member profiles. Many plugins available. Currently using the <a href="https://wordpress.org/plugins/bp-profile-search/" target="_blank">BP Profile Search</a> . Also, see UC3 for results ordering.

* **UC7 – Comment in Blog**<br>Just the basic functionality off-the-shelf functionality provided with WordPress. Only logged-in members can comment.

* **UC8 – Participate in Forum**<br>Functionality provided by bbPress. There is one general forum for all members. Pilots own private groups, using <a href="https://wordpress.org/plugins/bbp-private-groups/" target="_blank">bbP Private Groups</a> plugin.

* **UC9 – Private Group Chat**<br>The use case allows any pilot to have his/her own private group. There is only one such group chat for each pilot. Using <a href="https://wordpress.org/plugins/iflychat/" target="_blank">iFlyChat</a> plugin.

* **UC10 - Voice Video Chat**<br>TBD

* **UC11 - Points Accumulation**<br>TBD

## License
TBD

## How To Install
TBD

## How To Contribute
ASC is a massively ambitious project that is changing our approach to blah blah blah…. XXXXX Most primal and fundamental driving force to interact socially online.

## Roadmap
TBD

## Credits & Acknowledgements
(XXX needs editing and reduction in size) We use WordPress not just a CMS but as our application development platform. Our gratitude goes to Mr. Matt Mullenweg and all contributors for making it possible!
