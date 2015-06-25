<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Test validation of username');
$I->amOnPage('/');
$I->see('Register','li');
$I->click("//a[text()='Register']");
$I->seeCurrentUrlEquals('/register/');
$I->seeElement('input', ['id' => 'input_4_3']);
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('Please choose your nickname.');
$I->see('Please enter your email.');
$I->see('Please choose your password.');
$I->see('Please confirm if you are at least 21-years of age?');

// Username validation
$I->fillField('#input_4_3','Founder_Counselor');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('This nickname is already taken. Please choose another one.');
$I->fillField('#input_4_3','Founder>Counselor');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.");

// Email validation
$I->fillField('#input_4_22','Founder>Counselor');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('Please enter the valid email address.');
$I->fillField('#input_4_22','admin@alphasss.com');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('This email already in use. Please pick another one.');


// Password validation
$I->fillField('#input_4_4','awe3');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('The 2 passwords do not match. Please try again.');
$I->fillField('#input_4_4_2','awe3');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('Your password must be strong. It\'s for your own protection.');

// Age confirmation validation
$I->checkOption('#choice_4_8_1');
$I->click("//input[@id='gform_next_button_4_9']");
$I->dontSee('Please confirm if you are at least 21-years of age?');

