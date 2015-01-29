<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check register Main flow first step');
$I->amOnPage('/');
$I->see('Register','li');
$I->click('Register','li');
$I->seeCurrentUrlEquals('/register/');
$I->see('The Usual First Step');
$I->see("I daresay that Fry has discovered the smelliest object is the known universe! Throw her in brig. Also Zoidberg. Oh God, what I have done! Just once I'd like to eat dinner with a celebrity whi isn't bound and gagged. Daylight and everything.");
$I->see('Your Nickname', 'label');
$I->seeElement('input', ['value' => 'Next']);