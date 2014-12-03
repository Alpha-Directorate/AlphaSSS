# Alpha Social Club

*<a href="http://www.alphasocial.club" target="_blank">www.alphasocial.club</a>. Login:* `admin / Alpha.Omega`

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
We use WordPress not just a CMS but as our application development platform. Our gratitude goes to Mr. Matt Mullenweg and all contributors for making it possible!

![Alpha Social Club UML use case diagram](https://github.com/alpha-social-club/alpha-social-development/tree/master/images/ASC-Use-Case-Diagram-V1.png "Alpha Social Club UML use case diagram")

* **Actors**
  * **Visitor**<br>A user that is not recognized by the system as an existing member.

  * **Member**<br>A user who is recognized by (e.g. via a cookie) or currently logged-in to the system.

  * **Pilot**<br>A subtype of a member who can initiate a private group chat and voice video chat use cases. Regular member cannot do that.

  * **Admin**<br>This is equivalent to the WordPress administrator role. The all-powerful user.


* **UC1 – Invite Someone**<br>A visitor can only become a new member when invited by a current member. Presently, the invitation mechanism works by means of invitation codes.

* **UC2 – Register**<br>Simple form using Gravity Forms. Email address is not collected to satisfy the project vision, above. (Password recovery strategy will be described later.)

* **UC3 – Browse Listing**<br>There are a few important aspect to this: sorting, filtering and criteria. This use case calls UC11 "Points Accumulation" to sort the results output.

* **UC4 – Log In/Out**

* **UC5 – Edit Profile**

* **UC6 – Search**

* **UC7 – Comment in Blog**

* **UC8 – Participate in Forum**

* **UC9 – Private Group Chat**

* **UC10 - Voice Video Chat**

* **UC11 - Points Accumulation**

## Roadmap
TBD

## How to install
TBD

## How to contribute
TBD

## License
TBD