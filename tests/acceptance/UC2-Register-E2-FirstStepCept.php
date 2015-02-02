<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Test validation of username');
$I->amOnPage('/');
$I->see('Register','li');
$I->click('Register','li');
$I->seeCurrentUrlEquals('/register/');
$I->seeElement('input', ['id' => 'input_4_3']);
$I->click('Next','input');
$I->see('Please choose your nickname.');
$I->see('Please choose your password.');
$I->see('Please confirm if you are at least 21-years of age?');

// Username validation
$I->fillField('#input_4_3','Founder_Counselor');
$I->click('Next','input');
$I->see('This nickname is already taken. Please choose another one.');
$I->fillField('#input_4_3','Founder>Counselor');
$I->click('Next','input');
$I->see("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.");

// Password validation
$I->fillField('#input_4_4','awe3');
$I->click('Next','input');
$I->see('The 2 passwords do not match. Please try again.');
$I->fillField('#input_4_4_2','awe3');
$I->click('Next','input');
$I->see('Your password must be strong. It\'s for your own protection.');

// Age confirmation validation
$I->checkOption('#choice_4_8_1');
$I->click('Next','input');
$I->dontSee('Please confirm if you are at least 21-years of age?');

