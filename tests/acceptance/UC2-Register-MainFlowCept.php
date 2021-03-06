<?php 

$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Check register Main flow first step');
$I->amOnPage('/');
$I->see('Register','li');
$I->click("#menu-item-107 a");
$I->seeCurrentUrlEquals('/register/');
$I->see('Sign-up, the usual First Step...');
$I->see('Your Nickname', 'label');
$I->seeElement('input', ['id' => 'input_4_3']);
$I->see('Your Email Address', 'label');
$I->seeElement('input', ['id' => 'input_4_22']);
$I->seeElement('input', ['id' => 'choice_4_8_1']);
$I->seeElement('input', ['value' => 'Next']);

$username = substr(md5(time()), 0, 7);
$password = md5('password');

$I->fillField('#input_4_3',$username);
$I->fillField('#input_4_4', $password);
$I->fillField('#input_4_4_2', $password);
$I->fillField('#input_4_22', $username . '@yahoo.com');
$I->checkOption('#choice_4_8_1');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('Your Invitation Code','h1');

// Check is confirmation email is received
$I->receiveAnEmailWithSubject('Email Confirmation');
//$I->seeInEmailTextBody('Hello ' . $username . ',');
//$I->seeInEmailTextBody('To confirm your email, please click the link below');
$I->seeActivationLink();
$I->amOnPage($I->getActivationLink());

$I->fillField('log',$username);
$I->fillField('pwd', $password);
$I->click('Log In');
$I->seeCurrentUrlEquals('/register-pre-member/');
$I->dontSeeElement('#wp-admin-bar-user-credits');