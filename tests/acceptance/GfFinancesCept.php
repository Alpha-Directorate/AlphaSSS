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
$I->see('Finances', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Finances']");
$I->click('Accounting');
$I->wait(3);
$I->seeCurrentUrlEquals('/browse/elen/finances/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");