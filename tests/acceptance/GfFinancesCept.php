<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('see GF finances');

$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','elen');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/elen/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account-finances']");
$I->click('#wp-admin-bar-my-account-finances a');
$I->see('hello baby');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");