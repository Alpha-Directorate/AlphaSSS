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
$I->fillField('#input_4_3','Founder_Counselor');
$I->click('Next','input');
$I->see('This nickname is already taken. Please choose another one.');
$I->fillField('#input_4_3','Founder>Counselor');
$I->click('Next','input');
$I->see("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.");

