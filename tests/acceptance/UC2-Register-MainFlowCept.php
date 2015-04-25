<?php 

$I = new AcceptanceTester($scenario);
$I->wantTo('Check register Main flow first step');
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

$I->fillField('#input_4_3',$username);
$I->fillField('#input_4_4', md5('password'));
$I->fillField('#input_4_4_2', md5('password'));
$I->fillField('#input_4_22', md5(time()) . '@gmail.com');
$I->checkOption('#choice_4_8_1');
$I->click("//input[@id='gform_next_button_4_9']");
$I->see('Confirmation & Dire Warning!');
$I->see($username, '.red-data');
$I->see(md5('password'), '.red-data');
$I->checkOption('#choice_4_15_1');
$I->click("//input[@id='gform_next_button_4_11']");