<?php 

$I = new AcceptanceTester($scenario);
$I->wantTo('Check register Main flow first step');
$I->amOnPage('/');
$I->see('Register','li');
$I->click('Register','li');
$I->seeCurrentUrlEquals('/register/');
$I->see('The Usual First Step');
$I->fillField('#input_4_3','Founder_Counselor87');
$I->fillField('#input_4_4','andrei878787');
$I->fillField('#input_4_4_2','andrei878787');
$I->checkOption('#choice_4_8_1');
$I->click('input','#gform_next_button_4_9');
$I->see('Confirmation &amp; Dire Warning!');
//$I->see('Founder_Counselor87');
//$I->see('andrei878787');