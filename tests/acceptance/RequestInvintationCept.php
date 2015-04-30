<?php 
$I = new AcceptanceTester($scenario);

$elen = $I->haveFriend('elen');
$elen->does(function(AcceptanceTester $I) {
	$I->amOnPage('/');
	$I->resizeWindow(1024, 1024);
	$I->see('Login','.button');
	$I->click("//a[@class='button' and text()='Login']");
	$I->seeCurrentUrlEquals('/wp/wp-login.php');
	$I->fillField('log','elen');
	$I->fillField('pwd','funkadelicbro87');
	$I->click('Log In');
	$I->seeCurrentUrlEquals('/browse/elen/');
});

$I->wantTo('Check request invitanion module');
$I->amOnPage('/');
$I->see('Register','li');
$I->click("//a[text()='Register']");
$I->seeCurrentUrlEquals('/register/');
$I->see('The Usual First Step');
$I->see('Your Nickname', 'label');
$I->seeElement('input', ['id' => 'input_4_3']);
$I->see('Your Email Address', 'label');
$I->seeElement('input', ['id' => 'input_4_22']);
$I->seeElement('input', ['id' => 'choice_4_8_1']);
$I->seeElement('input', ['value' => 'Next']);

$username = md5(time());
$password = md5('password');

$I->fillField('#input_4_3',$username);
$I->fillField('#input_4_4', $password);
$I->fillField('#input_4_4_2', $password);
$I->fillField('#input_4_22', md5(time()) . '@yahoo.com');
$I->checkOption('#choice_4_8_1');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('Confirmation & Dire Warning!');
$I->see($username, '.red-data');
$I->see($password, '.red-data');
$I->checkOption('#choice_4_15_1');
$I->click("//input[@id='gform_next_button_4_11']");
$I->see('Your Invitation Code','h1');

$I->receiveAnEmailWithSubject('Email Confirmation');
$I->seeActivationLink();

$activation_link = $I->getActivationLink();

$I->amOnUrl($activation_link);
$I->fillField('log', $username);
$I->fillField('pwd', $password);
$I->click('Log In');
$I->seeCurrentUrlEquals('/register-pre-member/');
$I->see('Browse','li');
$I->click("//a[text()='Browse']");

$elen->does(function(AcceptanceTester $I) {
	$I->see('Browse','li');
	$I->click("//a[text()='Browse']");
});

$I->wait(3);
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/span[@class='activity']/div[@class='member-online']");
$I->see('Request Invitation', '#' . md5('elen'));
$I->click('Request Invitation', '#' . md5('elen'));
